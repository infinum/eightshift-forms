<?php

/**
 * Mailer Settings class.
 *
 * @package EightshiftForms\Integrations\Mailer
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailer;

use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Settings\SettingInterface;
use EightshiftForms\Settings\SettingGlobalInterface;
use EightshiftForms\Helpers\SettingsOutputHelpers;
use EightshiftForms\Integrations\AbstractSettingsIntegrations;
use EightshiftForms\Config\Config;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsMailer class.
 */
class SettingsMailer extends AbstractSettingsIntegrations implements SettingGlobalInterface, SettingInterface, ServiceInterface
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
	 * Mail To advanced append key.
	 */
	public const SETTINGS_MAILER_TO_ADVANCED_APPEND_KEY = 'mailer-to-advanced-append';

	/**
	 * Mail To advanced key.
	 */
	public const SETTINGS_MAILER_TO_ADVANCED_KEY = 'mailer-to-advanced';

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
	 * Send empty fields key.
	 */
	public const SETTINGS_MAILER_SEND_EMPTY_FIELDS_KEY = 'mailer-send-empty-fields';

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

		$isUsed = SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_MAILER_SETTINGS_USE_KEY, self::SETTINGS_MAILER_SETTINGS_USE_KEY, $formId);
		$name = SettingsHelpers::getSettingValue(self::SETTINGS_MAILER_SENDER_NAME_KEY, $formId);
		$email = SettingsHelpers::getSettingValue(self::SETTINGS_MAILER_SENDER_EMAIL_KEY, $formId);
		$to = SettingsHelpers::getSettingValue(self::SETTINGS_MAILER_TO_KEY, $formId);
		$subject = SettingsHelpers::getSettingValue(self::SETTINGS_MAILER_SUBJECT_KEY, $formId);
		$template = SettingsHelpers::getSettingValue(self::SETTINGS_MAILER_TEMPLATE_KEY, $formId);

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

		$emailField = SettingsHelpers::getSettingValue(self::SETTINGS_MAILER_EMAIL_FIELD_KEY, $formId);
		$isUsed = SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_MAILER_SENDER_USE_KEY, self::SETTINGS_MAILER_SENDER_USE_KEY, $formId);
		$name = SettingsHelpers::getSettingValue(self::SETTINGS_MAILER_SENDER_NAME_KEY, $formId);
		$email = SettingsHelpers::getSettingValue(self::SETTINGS_MAILER_SENDER_EMAIL_KEY, $formId);
		$subject = SettingsHelpers::getSettingValue(self::SETTINGS_MAILER_SENDER_SUBJECT_KEY, $formId);
		$template = SettingsHelpers::getSettingValue(self::SETTINGS_MAILER_SENDER_TEMPLATE_KEY, $formId);

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
		$isUsed = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_MAILER_USE_KEY, self::SETTINGS_MAILER_USE_KEY);

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
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		$formDetails = GeneralHelpers::getFormDetails($formId);
		$fieldNames = $formDetails[Config::FD_FIELD_NAMES];
		$fieldNameTags = SettingsOutputHelpers::getPartialFormFieldNames($fieldNames);
		$formResponseTags = SettingsOutputHelpers::getPartialFormResponseTags($formDetails[Config::FD_TYPE]);

		if ($formDetails[Config::FD_TYPE] !== self::SETTINGS_TYPE_KEY) {
			$formResponseTags .= SettingsOutputHelpers::getPartialFormResponseTags(self::SETTINGS_TYPE_KEY);
		}

		$isUsed = SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_MAILER_SETTINGS_USE_KEY, self::SETTINGS_MAILER_SETTINGS_USE_KEY, $formId);
		$isSenderUsed = SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_MAILER_SENDER_USE_KEY, self::SETTINGS_MAILER_SENDER_USE_KEY, $formId);
		$emailField = SettingsHelpers::getSettingValue(self::SETTINGS_MAILER_EMAIL_FIELD_KEY, $formId);

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
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
								'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_MAILER_SETTINGS_USE_KEY),
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
									'inputName' => SettingsHelpers::getSettingName(self::SETTINGS_MAILER_TO_KEY),
									'inputFieldLabel' => \__('Recipient e-mail', 'eightshift-forms'),
									// translators: %s will be replaced with forms field name.
									'inputFieldHelp' => \sprintf(\__('
										This e-mail address will be used to sent the form data to.<br />
										You can use multiple e-mails here by separating them by comma.<br /><br />
										%s', 'eightshift-forms'), SettingsOutputHelpers::getPartialFieldTags($fieldNameTags)),
									'inputType' => 'text',
									'inputPlaceholder' => 'info@infinum.com',
									'inputIsRequired' => true,
									'inputValue' => SettingsHelpers::getSettingValue(self::SETTINGS_MAILER_TO_KEY, $formId),
								],
								[
									'component' => 'textarea',
									'textareaName' => SettingsHelpers::getSettingName(self::SETTINGS_MAILER_TO_ADVANCED_KEY),
									'textareaFieldLabel' => \__('Recipient e-mail advanced', 'eightshift-forms'),
									'textareaIsMonospace' => true,
									'textareaSaveAsJson' => true,
									'textareaFieldHelp' => GeneralHelpers::minifyString(\__("
										Specify additional emails based on field values.<br />
										Provide one key-value pair per line, following this format: <code>email : conditions</code><br/>
										Conditions can be separated by <code>|</code> for OR and <code>&</code> for AND operators.<br/>
										If you are using multiple filed values they are separated by <code>---</code> and behave like OR condition.<br/>
										Negation is supported by adding <code>!=</code> to the condition.<br/>

										Example:
										<ul>
										<li>test1@infinum.com : rating=1&checkboxes=check-1---check-2</li>
										<li>test2@infinum.com : rating=2|select=option-1</li>
										<li>test3@infinum.com : rating!=3|select=option-2</li>
										<li>test4@infinum.com : rating=4</li>
										</ul>", 'eightshift-forms')),
									'textareaValue' => SettingsHelpers::getSettingValueAsJson(self::SETTINGS_MAILER_TO_ADVANCED_KEY, $formId),
								],
								[
									'component' => 'checkboxes',
									'checkboxesFieldLabel' => '',
									'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_MAILER_TO_ADVANCED_APPEND_KEY),
									'checkboxesContent' => [
										[
											'component' => 'checkbox',
											'checkboxLabel' => \__('Append to recipient e-mail', 'eightshift-forms'),
											'checkboxHelp' => \__('If checked, the advanced recipient e-mail will be appended to the default recipient email otherwise it will replace it.', 'eightshift-forms'),
											'checkboxIsChecked' => SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_MAILER_TO_ADVANCED_APPEND_KEY, self::SETTINGS_MAILER_TO_ADVANCED_APPEND_KEY, $formId),
											'checkboxValue' => self::SETTINGS_MAILER_TO_ADVANCED_APPEND_KEY,
											'checkboxSingleSubmit' => true,
											'checkboxAsToggle' => true,
										]
									]
								],
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => 'true',
								],
								[
									'component' => 'input',
									'inputName' => SettingsHelpers::getSettingName(self::SETTINGS_MAILER_SUBJECT_KEY),
									'inputFieldLabel' => \__('Subject', 'eightshift-forms'),
									// translators: %s will be replaced with forms field name.
									'inputFieldHelp' => \sprintf(\__('
										Specify e-mail subject with this field.<br /><br />
										%s', 'eightshift-forms'), SettingsOutputHelpers::getPartialFieldTags($fieldNameTags)),
									'inputType' => 'text',
									'inputIsRequired' => true,
									'inputValue' => SettingsHelpers::getSettingValue(self::SETTINGS_MAILER_SUBJECT_KEY, $formId),
								],
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => 'true',
								],
								[
									'component' => 'textarea',
									'textareaName' => SettingsHelpers::getSettingName(self::SETTINGS_MAILER_TEMPLATE_KEY),
									'textareaFieldLabel' => \__('Content', 'eightshift-forms'),
									// translators: %1$s will be replaced with forms field name, %2$s will be replaced with response tags, %3$s will be replaced with field names tags.
									'textareaFieldHelp' => \sprintf(\__('
										Specify e-mail body template with this field. You can use plain text or markdown.<br /><br />
										%1$s %2$s %3$s', 'eightshift-forms'), $this->getContentHelpOutput(), SettingsOutputHelpers::getPartialFieldTags($fieldNameTags), SettingsOutputHelpers::getPartialResponseTags($formResponseTags)),
									'textareaIsRequired' => true,
									'textareaValue' => SettingsHelpers::getSettingValue(self::SETTINGS_MAILER_TEMPLATE_KEY, $formId),
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
									'inputName' => SettingsHelpers::getSettingName(self::SETTINGS_MAILER_SENDER_NAME_KEY),
									'inputFieldLabel' => \__('E-mail client sender name', 'eightshift-forms'),
									'inputFieldHelp' => \__('Most e-mail clients will show this instead of the real address in the list of e-mails. Make this something related to your brand that will distinguish you from the rest.', 'eightshift-forms'),
									'inputType' => 'text',
									'inputIsRequired' => true,
									'inputValue' => SettingsHelpers::getSettingValue(self::SETTINGS_MAILER_SENDER_NAME_KEY, $formId),
								],
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => 'true',
								],
								[
									'component' => 'input',
									'inputName' => SettingsHelpers::getSettingName(self::SETTINGS_MAILER_SENDER_EMAIL_KEY),
									'inputFieldLabel' => \__('E-mail client from e-mail', 'eightshift-forms'),
									'inputFieldHelp' => \__('Most e-mail clients will us this as field as the sender e-mail (displayed as <i>From:</i>). It will also be used as a reply-to destination. Example: `info@infinum.com`.', 'eightshift-forms'),
									'inputType' => 'text',
									'inputIsEmail' => true,
									'inputIsRequired' => true,
									'inputValue' => SettingsHelpers::getSettingValue(self::SETTINGS_MAILER_SENDER_EMAIL_KEY, $formId),
								],
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => 'true',
								],
								[
									'component' => 'checkboxes',
									'checkboxesFieldLabel' => '',
									'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_MAILER_SEND_EMPTY_FIELDS_KEY),
									'checkboxesContent' => [
										[
											'component' => 'checkbox',
											'checkboxLabel' => \__('Send empty fields', 'eightshift-forms'),
											'checkboxHelp' => \__('If checked, all fields will be sent, even if they are empty.', 'eightshift-forms'),
											'checkboxIsChecked' => SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_MAILER_SEND_EMPTY_FIELDS_KEY, self::SETTINGS_MAILER_SEND_EMPTY_FIELDS_KEY, $formId),
											'checkboxValue' => self::SETTINGS_MAILER_SEND_EMPTY_FIELDS_KEY,
											'checkboxSingleSubmit' => true,
											'checkboxAsToggle' => true,
										]
									]
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
									'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_MAILER_SENDER_USE_KEY),
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
										'selectName' => SettingsHelpers::getSettingName(self::SETTINGS_MAILER_EMAIL_FIELD_KEY),
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
										'inputName' => SettingsHelpers::getSettingName(self::SETTINGS_MAILER_SENDER_SUBJECT_KEY),
										'inputFieldLabel' => \__('Subject', 'eightshift-forms'),
										// translators: %s will be replaced with forms field name.
										'inputFieldHelp' => \sprintf(\__('
											Specify confirmation e-mail subject with this field.<br /><br />
											%s', 'eightshift-forms'), SettingsOutputHelpers::getPartialFieldTags($fieldNameTags)),
										'inputType' => 'text',
										'inputIsRequired' => true,
										'inputValue' => SettingsHelpers::getSettingValue(self::SETTINGS_MAILER_SENDER_SUBJECT_KEY, $formId),
									],
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => 'true',
									],
									[
										'component' => 'textarea',
										'textareaName' => SettingsHelpers::getSettingName(self::SETTINGS_MAILER_SENDER_TEMPLATE_KEY),
										'textareaFieldLabel' => \__('E-mail content', 'eightshift-forms'),
										// translators: %s will be replaced with forms field name.
										'textareaFieldHelp' => \sprintf(\__('
											Specify confirmation e-mail body template with this field. You can use plain text or markdown.<br /><br />
											%1$s %2$s %3$s', 'eightshift-forms'), $this->getContentHelpOutput(), SettingsOutputHelpers::getPartialFieldTags($fieldNameTags), SettingsOutputHelpers::getPartialResponseTags($formResponseTags)),
										'textareaIsRequired' => true,
										'textareaValue' => SettingsHelpers::getSettingValue(self::SETTINGS_MAILER_SENDER_TEMPLATE_KEY, $formId),
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
		if (!SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_MAILER_USE_KEY, self::SETTINGS_MAILER_USE_KEY)) {
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
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
							...$this->getGlobalGeneralSettings(self::SETTINGS_TYPE_KEY),
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
		return GeneralHelpers::minifyString(\sprintf(\__("
			You can use markdown to provide additional styling to your email.
			If you need help with writing markdown, <a href='%1\$s' target='_blank' rel='noopener noreferrer'>take a look at this cheatsheet</a>.
			You can also use <a href='%2\$s' target='_blank' rel='noopener noreferrer'>this helper</a> to preview how your email will look like and is it valid.<br /><br />
			Note: <br />
			- <strong>If you want to add new line add two enters.</strong><br /><br />", 'eightshift-forms'), 'https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet', 'https://parsedown.org/demo'));
	}
}
