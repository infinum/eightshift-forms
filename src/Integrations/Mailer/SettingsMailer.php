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

		$name = $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_NAME_KEY, $formId);
		$email = $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_EMAIL_KEY, $formId);
		$to = $this->getSettingsValue(self::SETTINGS_MAILER_TO_KEY, $formId);
		$subject = $this->getSettingsValue(self::SETTINGS_MAILER_SUBJECT_KEY, $formId);
		$template = $this->getSettingsValue(self::SETTINGS_MAILER_TEMPLATE_KEY, $formId);

		if (!$name || !$email || !$to || !$subject || !$template) {
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

		$isUsed = $this->isCheckboxSettingsChecked(self::SETTINGS_MAILER_SENDER_USE_KEY, self::SETTINGS_MAILER_SENDER_USE_KEY, $formId);
		$name = $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_NAME_KEY, $formId);
		$email = $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_EMAIL_KEY, $formId);
		$subject = $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_SUBJECT_KEY, $formId);
		$template = $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_TEMPLATE_KEY, $formId);

		if (!$isUsed || !$name || !$email || !$subject || !$template) {
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

		$formNames = Helper::getFormFieldNames($formId);

		$isSenderUsed = $this->isCheckboxSettingsChecked(self::SETTINGS_MAILER_SENDER_USE_KEY, self::SETTINGS_MAILER_SENDER_USE_KEY, $formId);

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Sender', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_MAILER_SENDER_NAME_KEY),
								'inputFieldLabel' => \__('Name', 'eightshift-forms'),
								'inputFieldHelp' => \__('Most e-mail clients show this instead of the e-mail address in the list of e-mails', 'eightshift-forms'),
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
								'inputFieldLabel' => \__('E-mail', 'eightshift-forms'),
								'inputFieldHelp' => \__('Shown in the <code>From:</code> field', 'eightshift-forms'),
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
								'inputFieldLabel' => \__('Recipient', 'eightshift-forms'),
								// translators: %s will be replaced with forms field name.
								'inputFieldHelp' => \sprintf(\__('
									The e-mail will be sent to this address.<br /><br />
									Use template tags to use submitted form data (e.g. <code>{field-name}</code>)<br />
									<b>Make sure the field connected to the tag contains a valid e-mail address!</b>', 'eightshift-forms'), $formNames),
								'inputType' => 'text',
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
								'inputFieldHelp' => \sprintf(\__('Use template tags to use submitted form data (e.g. <code>{field-name}</code>)%s', 'eightshift-forms'), $formNames),
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
									Use template tags to use submitted form data (e.g. <code>{field-name}</code>)
									<details class="is-filter-applied">
										<summary>Available tags</summary>
										<ul>
											%1$s
										</ul>

										<br />
										Tag missing? Make sure its field has a <b>Name</b> set!
									</details>', 'eightshift-forms'), $formNames),
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
								'introSubtitle' => \__('Sent to the user that filled in the form, e.g. a "thank you" message.', 'eightshift-forms'),
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
										'checkboxLabel' => \__('Use confirmation email', 'eightshift-forms'),
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
									'inputFieldHelp' => \sprintf(\__('Use template tags to use submitted form data (e.g. <code>{field-name}</code>)', 'eightshift-forms'), $formNames),
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
									Use template tags to use submitted form data (e.g. <code>{field-name}</code>)
									<details class="is-filter-applied">
										<summary>Available tags</summary>
										<ul>
											%1$s
										</ul>

										<br />
										Tag missing? Make sure its field has a <b>Name</b> set!
									</details>', 'eightshift-forms'), $formNames),
									'textareaIsRequired' => true,
									'textareaValue' => $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_TEMPLATE_KEY, $formId),
								],
							] : []),
						],
					],
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
}
