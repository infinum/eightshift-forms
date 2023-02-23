<?php

/**
 * Mailer Settings class.
 *
 * @package EightshiftForms\Integrations\Mailer
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailer;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Settings\Settings\SettingInterface;
use EightshiftForms\Settings\Settings\SettingGlobalInterface;
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
						'tabLabel' => \__('Settings', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_MAILER_SENDER_NAME_KEY),
								'inputFieldLabel' => \__('Sender name', 'eightshift-forms'),
								'inputFieldHelp' => \__('Most e-mail clients show the sender name instead of the e-mail address in the list of e-mails.', 'eightshift-forms'),
								'inputType' => 'text',
								'inputIsRequired' => true,
								'inputValue' => $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_NAME_KEY, $formId),
							],
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_MAILER_SENDER_EMAIL_KEY),
								'inputFieldLabel' => \__('Sender e-mail', 'eightshift-forms'),
								'inputFieldHelp' => \__('Shows in the e-mail client as <strong>From:</strong>', 'eightshift-forms'),
								'inputType' => 'text',
								'inputIsEmail' => true,
								'inputIsRequired' => true,
								'inputValue' => $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_EMAIL_KEY, $formId),
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Email details', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introSubtitle' => \__('In these settings, your can define details when the email will be sent once the user submits.', 'eightshift-forms'),
							],
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_MAILER_TO_KEY),
								'inputFieldLabel' => \__('E-mail destination', 'eightshift-forms'),
								// translators: %s will be replaced with forms field name.
								'inputFieldHelp' => \sprintf(\__('
									The e-mail will be sent to this address.<br />
									Data from the form can be used in the form of template tags (<code>{field-name}</code>).<br />
									<b>WARNING: Be careful when using template tags and make sure that tag you are using contains a valid email address value.</b>', 'eightshift-forms'), $formNames),
								'inputType' => 'text',
								'inputIsRequired' => true,
								'inputValue' => $this->getSettingsValue(self::SETTINGS_MAILER_TO_KEY, $formId),
							],
							[
								'component' => 'input',
								'inputName' => $this->getSettingsName(self::SETTINGS_MAILER_SUBJECT_KEY),
								'inputFieldLabel' => \__('E-mail subject', 'eightshift-forms'),
								// translators: %s will be replaced with forms field name.
								'inputFieldHelp' => \sprintf(\__('Data from the form can be used in the form of template tags (<code>{field-name}</code>).', 'eightshift-forms'), $formNames),
								'inputType' => 'text',
								'inputIsRequired' => true,
								'inputValue' => $this->getSettingsValue(self::SETTINGS_MAILER_SUBJECT_KEY, $formId),
							],
							[
								'component' => 'textarea',
								'textareaName' => $this->getSettingsName(self::SETTINGS_MAILER_TEMPLATE_KEY),
								'textareaFieldLabel' => \__('E-mail content', 'eightshift-forms'),
								// translators: %s will be replaced with forms field name.
								'textareaFieldHelp' => \sprintf(\__('
									Data from the form can be used in the form of template tags (<code>{field-name}</code>).<br />
									If some tags are missing or you don\'t see any tags above, check that the <code>name</code> on the form field is set in the Form editor.<br />
									These tags are detected from the form:<br />
									%s', 'eightshift-forms'), $formNames),
								'textareaIsRequired' => true,
								'textareaValue' => $this->getSettingsValue(self::SETTINGS_MAILER_TEMPLATE_KEY, $formId),
							],
						]
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Confirmation emails', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introSubtitle' => \__('
									The confirmation mail is sent to the user that filled in the form, usually a "thank you" e-mail or similar.<br />
									Leave blank to disable the confirmation e-mail.', 'eightshift-forms'),
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
										'checkboxAsToggleSize' => 'medium',
									]
								]
							],
							...($isSenderUsed ? [
								[
									'component' => 'input',
									'inputName' => $this->getSettingsName(self::SETTINGS_MAILER_SENDER_SUBJECT_KEY),
									'inputFieldLabel' => \__('E-mail subject', 'eightshift-forms'),
									// translators: %s will be replaced with forms field name.
									'inputFieldHelp' => \sprintf(\__('Data from the form can be used in the form of template tags (<code>{field-name}</code>).', 'eightshift-forms'), $formNames),
									'inputType' => 'text',
									'inputIsRequired' => true,
									'inputValue' => $this->getSettingsValue(self::SETTINGS_MAILER_SENDER_SUBJECT_KEY, $formId),
								],
								[
									'component' => 'textarea',
									'textareaName' => $this->getSettingsName(self::SETTINGS_MAILER_SENDER_TEMPLATE_KEY),
									'textareaFieldLabel' => \__('E-mail content', 'eightshift-forms'),
									// translators: %s will be replaced with forms field name.
									'textareaFieldHelp' => \sprintf(\__('
										Data from the form can be used in the form of template tags (<code>{field-name}</code>).<br />
										If some tags are missing or you don\'t see any tags above, check that the <code>name</code> on the form field is set in the Form editor.<br />
										These tags are detected from the form:<br />
										%s', 'eightshift-forms'), $formNames),
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

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'intro',
				'introIsHighlighted' => true,
				'introSubtitle' => \__('Please keep in mind Mailer system uses the default WordPress mailing system. If you need to use something else, you need to configure it manually or use a plugin.', 'eightshift-forms'),
			],
		];
	}
}
