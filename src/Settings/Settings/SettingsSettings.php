<?php

/**
 * General Settings class.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Settings\FiltersOuputMock;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsSettings class.
 */
class SettingsSettings implements SettingGlobalInterface, ServiceInterface
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
	public const SETTINGS_GENERAL_DISABLE_AUTOINIT_ENQUEUE_SCRIPT_KEY = 'autoinit';
	public const SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_STYLE_KEY = 'styles';

	/**
	 * Disable scroll settings key.
	 */
	public const SETTINGS_GENERAL_DISABLE_SCROLL_KEY = 'general-disable-scroll';
	public const SETTINGS_GENERAL_DISABLE_SCROLL_TO_FIELD_ON_ERROR = 'disable-scroll-to-field-on-error';
	public const SETTINGS_GENERAL_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS = 'disable-scroll-to-global-message-on-success';
	public const SETTINGS_GENERAL_DISABLE_NATIVE_REDIRECT_ON_SUCCESS = 'disable-native-redirect-on-success';

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
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
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
								'checkboxesName' => $this->getSettingsName(self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY),
								'checkboxesIsToggles' => true,
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Disable default styles', 'eightshift-forms'),
										'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_STYLE_KEY, self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY),
										'checkboxValue' => self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_STYLE_KEY,
										'checkboxAsToggle' => true,
										'checkboxHelp' => \__('Includes all frontend and block editor styles.', 'eightshift-forms'),
									],
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Disable default scripts', 'eightshift-forms'),
										'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_SCRIPT_KEY, self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY),
										'checkboxValue' => self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_SCRIPT_KEY,
										'checkboxAsToggle' => true,
										'checkboxHelp' => \__('Includes all frontend logic, e.g. validation and form submission.', 'eightshift-forms'),
									],
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => true,
									],
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Don\'t auto-initialize scripts', 'eightshift-forms'),
										'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_GENERAL_DISABLE_AUTOINIT_ENQUEUE_SCRIPT_KEY, self::SETTINGS_GENERAL_DISABLE_DEFAULT_ENQUEUE_KEY),
										'checkboxValue' => self::SETTINGS_GENERAL_DISABLE_AUTOINIT_ENQUEUE_SCRIPT_KEY,
										'checkboxAsToggle' => true,
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
								'checkboxesName' => $this->getSettingsName(self::SETTINGS_GENERAL_DISABLE_SCROLL_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Don\'t scroll to the first field with an error', 'eightshift-forms'),
										'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_GENERAL_DISABLE_SCROLL_TO_FIELD_ON_ERROR, self::SETTINGS_GENERAL_DISABLE_SCROLL_KEY),
										'checkboxValue' => self::SETTINGS_GENERAL_DISABLE_SCROLL_TO_FIELD_ON_ERROR,
										'checkboxAsToggle' => true,
									],
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Don\'t scroll to the top of the form (to reveal the success message)', 'eightshift-forms'),
										'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_GENERAL_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS, self::SETTINGS_GENERAL_DISABLE_SCROLL_KEY),
										'checkboxValue' => self::SETTINGS_GENERAL_DISABLE_SCROLL_TO_GLOBAL_MESSAGE_ON_SUCCESS,
										'checkboxAsToggle' => true,
									],
									[
										'component' => 'divider',
										'dividerExtraVSpacing' => true,
									],
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Use JavaScript for redirects', 'eightshift-forms'),
										'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_GENERAL_DISABLE_NATIVE_REDIRECT_ON_SUCCESS, self::SETTINGS_GENERAL_DISABLE_SCROLL_KEY),
										'checkboxValue' => self::SETTINGS_GENERAL_DISABLE_NATIVE_REDIRECT_ON_SUCCESS,
										'checkboxAsToggle' => true,
									],
								],
							],
						],
					],
				],
			],
		];
	}
}
