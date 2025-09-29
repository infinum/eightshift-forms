<?php

/**
 * General Settings class.
 *
 * @package EightshiftForms\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings;

use EightshiftForms\Helpers\SettingsOutputHelpers;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Settings\SettingGlobalInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsSettings class.
 */
class SettingsSettings implements SettingGlobalInterface, ServiceInterface
{
	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_settings';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'settings';

	/**
	 * Disable default enqueue key.
	 */
	public const SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY = 'general-disable-default-enqueue';
	public const SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_SCRIPT_KEY = 'scripts';
	public const SETTINGS_GENERAL_DISABLE_AUTO_INIT_ENQUEUE_SCRIPT_KEY = 'autoinit';
	public const SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_STYLE_KEY = 'styles';

	/**
	 * Disable scroll settings key.
	 */
	public const SETTINGS_GENERAL_DISABLE_SCROLL_KEY = 'general-disable-scroll';
	public const SETTINGS_GENERAL_DISABLE_SCROLL_TO_FIELD_ON_ERROR = 'disable-scroll-to-field-on-error';
	public const SETTINGS_GENERAL_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS = 'disable-scroll-to-global-message-on-success';

	/**
	 * Accessibility settings key.
	 */
	public const SETTINGS_GENERAL_A11Y_KEY = 'general-a11y';
	public const SETTINGS_GENERAL_A11Y_DISABLE_SCROLL_TO_FIELD_KEY = 'disable-scroll-to-field-key';

	/**
	 * Hide global message timeout key.
	 */
	public const SETTINGS_GENERAL_HIDE_GLOBAL_MSG_TIMEOUT = 'hide-global-msg-timeout';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Scripts and styles', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldHideLabel' => true,
								'checkboxesName' => SettingsHelpers::getOptionName(self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY),
								'checkboxesIsToggles' => true,
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Disable default styles', 'eightshift-forms'),
										'checkboxIsChecked' => SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_STYLE_KEY, self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY),
										'checkboxValue' => self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_STYLE_KEY,
										'checkboxAsToggle' => true,
										'checkboxSingleSubmit' => true,
										'checkboxHelp' => \__('Includes all frontend and block editor styles.', 'eightshift-forms'),
									],
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Disable default scripts', 'eightshift-forms'),
										'checkboxIsChecked' => SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_SCRIPT_KEY, self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY),
										'checkboxValue' => self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_SCRIPT_KEY,
										'checkboxAsToggle' => true,
										'checkboxSingleSubmit' => true,
										'checkboxHelp' => \__('Includes all frontend logic, e.g. validation and form submission.', 'eightshift-forms'),
									],
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => true,
									],
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Don\'t auto-initialize scripts', 'eightshift-forms'),
										'checkboxIsChecked' => SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_GENERAL_DISABLE_AUTO_INIT_ENQUEUE_SCRIPT_KEY, self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY),
										'checkboxValue' => self::SETTINGS_GENERAL_DISABLE_AUTO_INIT_ENQUEUE_SCRIPT_KEY,
										'checkboxAsToggle' => true,
										'checkboxSingleSubmit' => true,
										'checkboxHelp' => \__('Don\'t auto-initialize scripts will load all the scripts, but not initialize them. To learn how to do it manually refer to the documentation.', 'eightshift-forms'),
									],
								],
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('After form submit', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldHideLabel' => true,
								'checkboxesName' => SettingsHelpers::getOptionName(self::SETTINGS_GENERAL_DISABLE_SCROLL_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Don\'t scroll to the first field with an error', 'eightshift-forms'),
										'checkboxIsChecked' => SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_GENERAL_DISABLE_SCROLL_TO_FIELD_ON_ERROR, self::SETTINGS_GENERAL_DISABLE_SCROLL_KEY),
										'checkboxValue' => self::SETTINGS_GENERAL_DISABLE_SCROLL_TO_FIELD_ON_ERROR,
										'checkboxAsToggle' => true,
										'checkboxSingleSubmit' => true,
									],
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Don\'t scroll to the top of the form (to reveal the success message)', 'eightshift-forms'),
										'checkboxIsChecked' => SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_GENERAL_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS, self::SETTINGS_GENERAL_DISABLE_SCROLL_KEY),
										'checkboxValue' => self::SETTINGS_GENERAL_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS,
										'checkboxAsToggle' => true,
										'checkboxSingleSubmit' => true,
									],
								],
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Accessibility', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldHideLabel' => true,
								'checkboxesName' => SettingsHelpers::getOptionName(self::SETTINGS_GENERAL_A11Y_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Disable scroll to field on focus', 'eightshift-forms'),
										'checkboxHelp' => \__('Affected fields are: select, country, phone, date, dateTime.', 'eightshift-forms'),
										'checkboxIsChecked' => SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_GENERAL_A11Y_DISABLE_SCROLL_TO_FIELD_KEY, self::SETTINGS_GENERAL_A11Y_KEY),
										'checkboxValue' => self::SETTINGS_GENERAL_A11Y_DISABLE_SCROLL_TO_FIELD_KEY,
										'checkboxAsToggle' => true,
										'checkboxSingleSubmit' => true,
									],
								],
							],
							[
								'component' => 'input',
								'inputName' => SettingsHelpers::getOptionName(self::SETTINGS_GENERAL_HIDE_GLOBAL_MSG_TIMEOUT),
								'inputFieldLabel' => \__('Hide global message timeout', 'eightshift-forms'),
								'inputFieldHelp' => \__("The amount of time the global message is displayed. If you don't want to hide the global message, set it to a high value.", 'eightshift-forms'),
								'inputType' => 'number',
								'inputMin' => 1,
								'inputStep' => 1,
								'inputPlaceholder' => 6,
								'inputFieldAfterContent' => \__('sec', 'eightshift-forms'),
								'inputFieldInlineBeforeAfterContent' => true,
								'inputValue' => SettingsHelpers::getOptionValue(self::SETTINGS_GENERAL_HIDE_GLOBAL_MSG_TIMEOUT),
							],
						],
					],
				],
			],
		];
	}
}
