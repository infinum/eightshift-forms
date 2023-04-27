<?php

/**
 * Mailer Settings class.
 *
 * @package EightshiftForms\Integrations\Mailer
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailer;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Settings\FiltersOuputMock;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Settings\Settings\SettingInterface;
use EightshiftForms\Settings\Settings\SettingGlobalInterface;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsMailer class.
 */
class SettingsMailer implements SettingInterface, SettingGlobalInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Use general helper trait.
	 */
	use FiltersOuputMock;

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_mailer';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_mailer';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_mailer';

	/**
	 * Filter settings is valid confirmation key.
	 */
	public const FILTER_SETTINGS_IS_VALID_CONFIRMATION_NAME = 'es_forms_settings_is_valid_confirmation_mailer';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'mailer';

	/**
	 * Settings type custom key.
	 */
	public const SETTINGS_TYPE_CUSTOM_KEY = 'custom';

	/**
	 * Mailer Use key.
	 */
	public const SETTINGS_MAILER_USE_KEY = 'mailer-use';

	/**
	 * Email field key.
	 */
	public const SETTINGS_MAILER_EMAIL_FIELD_KEY = 'mailer-email-field';

	/**
	 * Sender Name key.
	 */
	public const SETTINGS_MAILER_SENDER_NAME_KEY = 'mailer-sender-name';

	/**
	 * Sender Email key.
	 */
	public const SETTINGS_MAILER_SENDER_EMAIL_KEY = 'mailer-sender-email';

	/**
	 * Mail To key.
	 */
	public const SETTINGS_MAILER_TO_KEY = 'mailer-to';

	/**
	 * Subject key.
	 */
	public const SETTINGS_MAILER_SUBJECT_KEY = 'mailer-subject';

	/**
	 * Template key.
	 */
	public const SETTINGS_MAILER_TEMPLATE_KEY = 'mailer-template';

	/**
	 * Sender use key.
	 */
	public const SETTINGS_MAILER_SENDER_USE_KEY = 'mailer-sender-use';

	/**
	 * Sender Subject key.
	 */
	public const SETTINGS_MAILER_SENDER_SUBJECT_KEY = 'mailer-sender-subject';

	/**
	 * Sender Template key.
	 */
	public const SETTINGS_MAILER_SENDER_TEMPLATE_KEY = 'mailer-sender-template';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_IS_VALID_NAME, [$this, 'isSettingsValid']);
		\add_filter(self::FILTER_SETTINGS_IS_VALID_CONFIRMATION_NAME, [$this, 'isSettingsConfirmationValid']);
	}

	/**
	 * Determine if settings are valid.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return boolean
	 */
	public function isSettingsValid(string $formId): bool
	{
		if (!$this->isSettingsGlobalValid()) {
			return false;
		}

		$emailField = $this->getSettingsValue(self::SETTINGS_MAILER_EMAIL_FIELD_KEY, $formId);
		$name = $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_NAME_KEY, $formId);
		$email = $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_EMAIL_KEY, $formId);
		$to = $this->getSettingsValue(self::SETTINGS_MAILER_TO_KEY, $formId);
		$subject = $this->getSettingsValue(self::SETTINGS_MAILER_SUBJECT_KEY, $formId);
		$template = $this->getSettingsValue(self::SETTINGS_MAILER_TEMPLATE_KEY, $formId);

		if (!$name || !$email || !$to || !$subject || !$template || !$emailField) {
			return false;
		}

		return true;
	}

	/**
	 * Determine if confirmation settings are valid.
	 *
	 * @param string $formId Form ID.
	 *
	 * @return boolean
	 */
	public function isSettingsConfirmationValid(string $formId): bool
	{
		if (!$this->isSettingsGlobalValid()) {
			return false;
		}

		$emailField = $this->getSettingsValue(self::SETTINGS_MAILER_EMAIL_FIELD_KEY, $formId);
		$isUsed = $this->isCheckboxSettingsChecked(self::SETTINGS_MAILER_SENDER_USE_KEY, self::SETTINGS_MAILER_SENDER_USE_KEY, $formId);
		$name = $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_NAME_KEY, $formId);
		$email = $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_EMAIL_KEY, $formId);
		$subject = $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_SUBJECT_KEY, $formId);
		$template = $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_TEMPLATE_KEY, $formId);

		if (!$isUsed || !$name || !$email || !$subject || !$template || !$emailField) {
			return false;
		}

		return true;
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_MAILER_USE_KEY, self::SETTINGS_MAILER_USE_KEY);

		if (!$isUsed) {
			return false;
		}

		return true;
	}

	/**
	 * Get Form settings data array
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsData(string $formId): array
	{
		// Bailout if feature is not active.
		if (!$this->isSettingsGlobalValid()) {
			return $this->getNoActiveFeatureOutput();
		}

		$formDetails = Helper::getFormDetailsById($formId);

		$fieldNames = $formDetails['fieldNames'];
		$fieldNameTags = Helper::getFormFieldNames($fieldNames);
		$formResponseTags = Helper::getFormResponseTags($formDetails['typeFilter']);

		$isSenderUsed = $this->isCheckboxSettingsChecked(self::SETTINGS_MAILER_SENDER_USE_KEY, self::SETTINGS_MAILER_SENDER_USE_KEY, $formId);
		$emailField = $this->getSettingsValue(self::SETTINGS_MAILER_EMAIL_FIELD_KEY, $formId);

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Settings', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'select',
								'selectSingleSubmit' => true,
								'selectName' => $this->getSettingsName(self::SETTINGS_MAILER_EMAIL_FIELD_KEY),
								'selectFieldHelp' => \__('You must select what field is used as an e-mail.', 'eightshift-forms'),
								'selectFieldLabel' => \__('E-mail field', 'eightshift-forms'),
								'selectContent' => \array_merge(
									[
										[
											'component' => 'select-option',
											'selectOptionLabel' => '',
											'selectOptionValue' => '',
										],
									],
									\array_map(
										static function ($option) use ($emailField) {
											return [
												'component' => 'select-option',
												'selectOptionLabel' => $option,
												'selectOptionValue' => $option,
												'selectOptionIsSelected' => $emailField === $option,
											];
										},
										$fieldNames
									)
								),
							],
						],
					],
					...($emailField ? [
						[
							'component' => 'tab',
							'tabLabel' => \__('Advanced', 'eightshift-forms'),
							'tabContent' => [
								[
									'component' => 'input',
									'inputName' => $this->getSettingsName(self::SETTINGS_MAILER_SENDER_NAME_KEY),
									'inputFieldLabel' => \__('E-mail client sender name', 'eightshift-forms'),
									'inputFieldHelp' => \__('Most e-mail clients will show this instead of the real address in the list of e-mails. Make this something related to your brand that will distinguish you from the rest.', 'eightshift-forms'),
									'inputType' => 'text',
									'inputIsRequired' => true,
									'inputValue' => $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_NAME_KEY, $formId),
								],
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => 'true',
								],
								[
									'component' => 'input',
									'inputName' => $this->getSettingsName(self::SETTINGS_MAILER_SENDER_EMAIL_KEY),
									'inputFieldLabel' => \__('E-mail client from e-mail', 'eightshift-forms'),
									'inputFieldHelp' => \__('Most e-mail clients will us this as field as the sender e-mail (displayed as <i>From:</i>). It will also be used as a reply-to destination. Example: `info@infinum.com`.', 'eightshift-forms'),
									'inputType' => 'text',
									'inputIsEmail' => true,
									'inputIsRequired' => true,
									'inputValue' => $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_EMAIL_KEY, $formId),
								],
							],
						],
						[
							'component' => 'tab',
							'tabLabel' => \__('E-mail', 'eightshift-forms'),
							'tabContent' => [
								[
									'component' => 'input',
									'inputName' => $this->getSettingsName(self::SETTINGS_MAILER_TO_KEY),
									'inputFieldLabel' => \__('Recipient e-mail', 'eightshift-forms'),
									// translators: %s will be replaced with forms field name.
									'inputFieldHelp' => \sprintf(\__('
										This e-mail address will be used to sent the form data to.<br />
										You can use multiple e-mails here by separating them by comma.<br /><br />
										%s', 'eightshift-forms'), $this->getFieldTagsOutput($fieldNameTags)),
									'inputType' => 'text',
									'inputPlaceholder' => 'info@infinum.com',
									'inputIsRequired' => true,
									'inputValue' => $this->getSettingsValue(self::SETTINGS_MAILER_TO_KEY, $formId),
								],
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => 'true',
								],
								[
									'component' => 'input',
									'inputName' => $this->getSettingsName(self::SETTINGS_MAILER_SUBJECT_KEY),
									'inputFieldLabel' => \__('Subject', 'eightshift-forms'),
									// translators: %s will be replaced with forms field name.
									'inputFieldHelp' => \sprintf(\__('
										Specify e-mail subject with this field.<br /><br />
										%s', 'eightshift-forms'), $this->getFieldTagsOutput($fieldNameTags)),
									'inputType' => 'text',
									'inputIsRequired' => true,
									'inputValue' => $this->getSettingsValue(self::SETTINGS_MAILER_SUBJECT_KEY, $formId),
								],
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => 'true',
								],
								[
									'component' => 'textarea',
									'textareaName' => $this->getSettingsName(self::SETTINGS_MAILER_TEMPLATE_KEY),
									'textareaFieldLabel' => \__('Content', 'eightshift-forms'),
									// translators: %s will be replaced with forms field name.
									'textareaFieldHelp' => \sprintf(\__('
										Specify e-mail body template with this field. You can use plain text of simple HTML tags.<br /><br />
										%1$s %2$s', 'eightshift-forms'), $this->getFieldTagsOutput($fieldNameTags), $this->getResponseTagsOutput($formResponseTags)),
									'textareaIsRequired' => true,
									'textareaValue' => $this->getSettingsValue(self::SETTINGS_MAILER_TEMPLATE_KEY, $formId),
								],
							]
						],
						[
							'component' => 'tab',
							'tabLabel' => \__('Confirmation e-mail', 'eightshift-forms'),
							'tabContent' => [
								[
									'component' => 'intro',
									'introSubtitle' => \__('Send your users a "thank you" message after they submit the form.', 'eightshift-forms'),
								],
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => 'true',
								],
								[
									'component' => 'checkboxes',
									'checkboxesFieldLabel' => '',
									'checkboxesName' => $this->getSettingsName(self::SETTINGS_MAILER_SENDER_USE_KEY),
									'checkboxesContent' => [
										[
											'component' => 'checkbox',
											'checkboxLabel' => \__('Send a confirmation e-mail', 'eightshift-forms'),
											'checkboxIsChecked' => $isSenderUsed,
											'checkboxValue' => self::SETTINGS_MAILER_SENDER_USE_KEY,
											'checkboxSingleSubmit' => true,
											'checkboxAsToggle' => true,
										]
									]
								],
								...($isSenderUsed ? [
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => 'true',
									],
									[
										'component' => 'input',
										'inputName' => $this->getSettingsName(self::SETTINGS_MAILER_SENDER_SUBJECT_KEY),
										'inputFieldLabel' => \__('Subject', 'eightshift-forms'),
										// translators: %s will be replaced with forms field name.
										'inputFieldHelp' => \sprintf(\__('
											Specify confirmation e-mail subject with this field.<br /><br />
											%s', 'eightshift-forms'), $this->getFieldTagsOutput($fieldNameTags)),
										'inputType' => 'text',
										'inputIsRequired' => true,
										'inputValue' => $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_SUBJECT_KEY, $formId),
									],
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => 'true',
									],
									[
										'component' => 'textarea',
										'textareaName' => $this->getSettingsName(self::SETTINGS_MAILER_SENDER_TEMPLATE_KEY),
										'textareaFieldLabel' => \__('E-mail content', 'eightshift-forms'),
										// translators: %s will be replaced with forms field name.
										'textareaFieldHelp' => \sprintf(\__('
											Specify confirmation e-mail body template with this field. You can use plain text of simple HTML tags.<br /><br />
											%1$s %2$s', 'eightshift-forms'), $this->getFieldTagsOutput($fieldNameTags), $this->getResponseTagsOutput($formResponseTags)),
										'textareaIsRequired' => true,
										'textareaValue' => $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_TEMPLATE_KEY, $formId),
									],
								] : []),
							],
						],
					] : []),
				],
			],
		];
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		// Bailout if feature is not active.
		if (!$this->isCheckboxOptionChecked(self::SETTINGS_MAILER_USE_KEY, self::SETTINGS_MAILER_USE_KEY)) {
			return $this->getNoActiveFeatureOutput();
		}

		$successRedirectUrl = $this->getSuccessRedirectUrlFilterValue(self::SETTINGS_TYPE_KEY, '');

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-card',
				'layoutContent' => [
					[
						'component' => 'intro',
						'introSubtitle' => \__('Mailer uses the built-in WordPress mailing system.<br /><br />If using an external mailing service, configure it manually or through a plugin.', 'eightshift-forms'),
					],
				],
			],
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('General', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_TYPE_KEY . '-' . SettingsGeneral::SETTINGS_GLOBAL_REDIRECT_SUCCESS_KEY),
								'inputFieldLabel' => \__('After submit redirect URL', 'eightshift-forms'),
								// translators: %s will be replaced with forms field name and filter output copy.
								'inputFieldHelp' => \sprintf(\__('
									If URL is provided, after a successful submission the user is redirected to the provided URL and the success message will <strong>not</strong> show.
									<br />
									%s', 'eightshift-forms'), $successRedirectUrl['settingsGlobal']),
								'inputType' => 'url',
								'inputIsUrl' => true,
								'inputIsDisabled' => $successRedirectUrl['filterUsedGlobal'],
								'inputValue' => $successRedirectUrl['dataGlobal'],
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Get response tags output copy.
	 *
	 * @param string $formFieldTags Response tags to output.
	 *
	 * @return string
	 */
	private function getFieldTagsOutput(string $formFieldTags): string
	{
		if (!$formFieldTags) {
			return '';
		}

		// translators: %s will be replaced with form field names.
		return \sprintf(\__('
			Use template tags to use submitted form data (e.g. <code>{field-name}</code>)
			<details class="is-filter-applied">
				<summary>Available tags</summary>
				<ul>
					%s
				</ul>
				<br />
				Tag missing? Make sure its field has a <b>Name</b> set!
			</details>', 'eightshift-forms'), $formFieldTags);
	}

	/**
	 * Get response tags copy output.
	 *
	 * @param string $formResponseTags Response tags to output.
	 *
	 * @return string
	 */
	private function getResponseTagsOutput(string $formResponseTags): string
	{
		if (!$formResponseTags) {
			return '';
		}

		// translators: %s will be replaced with integration response tags.
		return \sprintf(\__('
			<details class="is-filter-applied">
				<summary>Response tags</summary>
				<ul>
					%s
				</ul>
				<br />
				Use response tags to populate the content with the data that the integration sends back.
			</details>', 'eightshift-forms'), $formResponseTags);
	}
}
