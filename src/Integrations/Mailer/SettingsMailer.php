<?php

/**
 * Mailer Settings class.
 *
 * @package EightshiftForms\Integrations\Mailer
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailer;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftForms\General\SettingsGeneral;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftForms\Hooks\FiltersOuputMock;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsMailer class.
 */
class SettingsMailer implements UtilsSettingGlobalInterface, UtilsSettingInterface, ServiceInterface
{
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
	 * Mailer settings Use key.
	 */
	public const SETTINGS_MAILER_SETTINGS_USE_KEY = 'mailer-settings-use';

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

		$isUsed = UtilsSettingsHelper::isSettingCheckboxChecked(self::SETTINGS_MAILER_SETTINGS_USE_KEY, self::SETTINGS_MAILER_SETTINGS_USE_KEY, $formId);
		$name = UtilsSettingsHelper::getSettingValue(self::SETTINGS_MAILER_SENDER_NAME_KEY, $formId);
		$email = UtilsSettingsHelper::getSettingValue(self::SETTINGS_MAILER_SENDER_EMAIL_KEY, $formId);
		$to = UtilsSettingsHelper::getSettingValue(self::SETTINGS_MAILER_TO_KEY, $formId);
		$subject = UtilsSettingsHelper::getSettingValue(self::SETTINGS_MAILER_SUBJECT_KEY, $formId);
		$template = UtilsSettingsHelper::getSettingValue(self::SETTINGS_MAILER_TEMPLATE_KEY, $formId);

		if (!$isUsed || !$name || !$email || !$to || !$subject || !$template) {
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

		$emailField = UtilsSettingsHelper::getSettingValue(self::SETTINGS_MAILER_EMAIL_FIELD_KEY, $formId);
		$isUsed = UtilsSettingsHelper::isSettingCheckboxChecked(self::SETTINGS_MAILER_SENDER_USE_KEY, self::SETTINGS_MAILER_SENDER_USE_KEY, $formId);
		$name = UtilsSettingsHelper::getSettingValue(self::SETTINGS_MAILER_SENDER_NAME_KEY, $formId);
		$email = UtilsSettingsHelper::getSettingValue(self::SETTINGS_MAILER_SENDER_EMAIL_KEY, $formId);
		$subject = UtilsSettingsHelper::getSettingValue(self::SETTINGS_MAILER_SENDER_SUBJECT_KEY, $formId);
		$template = UtilsSettingsHelper::getSettingValue(self::SETTINGS_MAILER_SENDER_TEMPLATE_KEY, $formId);

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
		$isUsed = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_MAILER_USE_KEY, self::SETTINGS_MAILER_USE_KEY);

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
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		$formDetails = UtilsGeneralHelper::getFormDetails($formId);

		$fieldNames = $formDetails[UtilsConfig::FD_FIELD_NAMES_TAGS];
		$fieldNameTags = UtilsSettingsOutputHelper::getPartialFormFieldNames($fieldNames);
		$formResponseTags = UtilsSettingsOutputHelper::getPartialFormResponseTags($formDetails[UtilsConfig::FD_TYPE]);

		$isUsed = UtilsSettingsHelper::isSettingCheckboxChecked(self::SETTINGS_MAILER_SETTINGS_USE_KEY, self::SETTINGS_MAILER_SETTINGS_USE_KEY, $formId);
		$isSenderUsed = UtilsSettingsHelper::isSettingCheckboxChecked(self::SETTINGS_MAILER_SENDER_USE_KEY, self::SETTINGS_MAILER_SENDER_USE_KEY, $formId);
		$emailField = UtilsSettingsHelper::getSettingValue(self::SETTINGS_MAILER_EMAIL_FIELD_KEY, $formId);

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('E-mail', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_MAILER_SETTINGS_USE_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Use mailer', 'eightshift-forms'),
										'checkboxIsChecked' => $isUsed,
										'checkboxValue' => self::SETTINGS_MAILER_SETTINGS_USE_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									]
								]
							],
							...($isUsed ? [
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => 'true',
								],
								[
									'component' => 'input',
									'inputName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_MAILER_TO_KEY),
									'inputFieldLabel' => \__('Recipient e-mail', 'eightshift-forms'),
									// translators: %s will be replaced with forms field name.
									'inputFieldHelp' => \sprintf(\__('
										This e-mail address will be used to sent the form data to.<br />
										You can use multiple e-mails here by separating them by comma.<br /><br />
										%s', 'eightshift-forms'), UtilsSettingsOutputHelper::getPartialFieldTags($fieldNameTags)),
									'inputType' => 'text',
									'inputPlaceholder' => 'info@infinum.com',
									'inputIsRequired' => true,
									'inputValue' => UtilsSettingsHelper::getSettingValue(self::SETTINGS_MAILER_TO_KEY, $formId),
								],
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => 'true',
								],
								[
									'component' => 'input',
									'inputName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_MAILER_SUBJECT_KEY),
									'inputFieldLabel' => \__('Subject', 'eightshift-forms'),
									// translators: %s will be replaced with forms field name.
									'inputFieldHelp' => \sprintf(\__('
										Specify e-mail subject with this field.<br /><br />
										%s', 'eightshift-forms'), UtilsSettingsOutputHelper::getPartialFieldTags($fieldNameTags)),
									'inputType' => 'text',
									'inputIsRequired' => true,
									'inputValue' => UtilsSettingsHelper::getSettingValue(self::SETTINGS_MAILER_SUBJECT_KEY, $formId),
								],
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => 'true',
								],
								[
									'component' => 'textarea',
									'textareaName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_MAILER_TEMPLATE_KEY),
									'textareaFieldLabel' => \__('Content', 'eightshift-forms'),
									// translators: %s will be replaced with forms field name.
									'textareaFieldHelp' => \sprintf(\__('
										Specify e-mail body template with this field. You can use plain text or markdown.<br /><br />
										%1$s %2$s %3$s', 'eightshift-forms'), $this->getContentHelpOutput(), UtilsSettingsOutputHelper::getPartialFieldTags($fieldNameTags), UtilsSettingsOutputHelper::getPartialResponseTags($formResponseTags)),
									'textareaIsRequired' => true,
									'textareaValue' => UtilsSettingsHelper::getSettingValue(self::SETTINGS_MAILER_TEMPLATE_KEY, $formId),
								],
							] : []),
						]
					],
					...($isUsed ? [
						[
							'component' => 'tab',
							'tabLabel' => \__('Advanced', 'eightshift-forms'),
							'tabContent' => [
								[
									'component' => 'input',
									'inputName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_MAILER_SENDER_NAME_KEY),
									'inputFieldLabel' => \__('E-mail client sender name', 'eightshift-forms'),
									'inputFieldHelp' => \__('Most e-mail clients will show this instead of the real address in the list of e-mails. Make this something related to your brand that will distinguish you from the rest.', 'eightshift-forms'),
									'inputType' => 'text',
									'inputIsRequired' => true,
									'inputValue' => UtilsSettingsHelper::getSettingValue(self::SETTINGS_MAILER_SENDER_NAME_KEY, $formId),
								],
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => 'true',
								],
								[
									'component' => 'input',
									'inputName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_MAILER_SENDER_EMAIL_KEY),
									'inputFieldLabel' => \__('E-mail client from e-mail', 'eightshift-forms'),
									'inputFieldHelp' => \__('Most e-mail clients will us this as field as the sender e-mail (displayed as <i>From:</i>). It will also be used as a reply-to destination. Example: `info@infinum.com`.', 'eightshift-forms'),
									'inputType' => 'text',
									'inputIsEmail' => true,
									'inputIsRequired' => true,
									'inputValue' => UtilsSettingsHelper::getSettingValue(self::SETTINGS_MAILER_SENDER_EMAIL_KEY, $formId),
								],
							],
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
									'checkboxesName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_MAILER_SENDER_USE_KEY),
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
										'component' => 'select',
										'selectSingleSubmit' => true,
										'selectName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_MAILER_EMAIL_FIELD_KEY),
										'selectFieldHelp' => \__('You must select what field is used as an e-mail.', 'eightshift-forms'),
										'selectFieldLabel' => \__('E-mail field', 'eightshift-forms'),
										'selectPlaceholder' => \__('Select email field', 'eightshift-forms'),
										'selectContent' => \array_map(
											static function ($option) use ($emailField) {
												return [
													'component' => 'select-option',
													'selectOptionLabel' => $option,
													'selectOptionValue' => $option,
													'selectOptionIsSelected' => $emailField === $option,
												];
											},
											$fieldNames
										),
									],
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => 'true',
									],
									[
										'component' => 'input',
										'inputName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_MAILER_SENDER_SUBJECT_KEY),
										'inputFieldLabel' => \__('Subject', 'eightshift-forms'),
										// translators: %s will be replaced with forms field name.
										'inputFieldHelp' => \sprintf(\__('
											Specify confirmation e-mail subject with this field.<br /><br />
											%s', 'eightshift-forms'), UtilsSettingsOutputHelper::getPartialFieldTags($fieldNameTags)),
										'inputType' => 'text',
										'inputIsRequired' => true,
										'inputValue' => UtilsSettingsHelper::getSettingValue(self::SETTINGS_MAILER_SENDER_SUBJECT_KEY, $formId),
									],
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => 'true',
									],
									[
										'component' => 'textarea',
										'textareaName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_MAILER_SENDER_TEMPLATE_KEY),
										'textareaFieldLabel' => \__('E-mail content', 'eightshift-forms'),
										// translators: %s will be replaced with forms field name.
										'textareaFieldHelp' => \sprintf(\__('
											Specify confirmation e-mail body template with this field. You can use plain text or markdown.<br /><br />
											%1$s %2$s %3$s', 'eightshift-forms'), $this->getContentHelpOutput(), UtilsSettingsOutputHelper::getPartialFieldTags($fieldNameTags), UtilsSettingsOutputHelper::getPartialResponseTags($formResponseTags)),
										'textareaIsRequired' => true,
										'textareaValue' => UtilsSettingsHelper::getSettingValue(self::SETTINGS_MAILER_SENDER_TEMPLATE_KEY, $formId),
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
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_MAILER_USE_KEY, self::SETTINGS_MAILER_USE_KEY)) {
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		$successRedirectUrl = FiltersOuputMock::getSuccessRedirectUrlFilterValue(self::SETTINGS_TYPE_KEY, '');

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
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
								'inputName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_TYPE_KEY . '-' . SettingsGeneral::SETTINGS_GLOBAL_REDIRECT_SUCCESS_KEY),
								'inputFieldLabel' => \__('After submit redirect URL', 'eightshift-forms'),
								// translators: %s will be replaced with forms field name and filter output copy.
								'inputFieldHelp' => \sprintf(\__('
									If URL is provided, after a successful submission the user is redirected to the provided URL and the success message will <strong>not</strong> show.
									<br />
									%s', 'eightshift-forms'), $successRedirectUrl['settingsGlobal']),
								'inputType' => 'url',
								'inputIsUrl' => true,
								'inputValue' => $successRedirectUrl['dataGlobal'],
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Provide additional markdown copy to help.
	 *
	 * @return string
	 */
	private function getContentHelpOutput(): string
	{
		return UtilsGeneralHelper::minifyString(\sprintf(\__("
			You can use markdown to provide additional styling to your email.
			If you need help with writing markdown, <a href='%1\$s' target='_blank' rel='noopener noreferrer'>take a look at this cheatsheet</a>.
			You can also use <a href='%2\$s' target='_blank' rel='noopener noreferrer'>this helper</a> to preview how your email will look like and is it valid.<br /><br />
			Note: <br />
			- <strong>If you want to add new line add two enters.</strong><br /><br />", 'eightshift-forms'), 'https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet', 'https://parsedown.org/demo'));
	}
}
