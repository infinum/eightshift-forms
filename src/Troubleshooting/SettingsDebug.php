<?php

/**
 * Debug Settings class.
 *
 * @package EightshiftForms\Troubleshooting
 */

declare(strict_types=1);

namespace EightshiftForms\Troubleshooting;

use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsDebug class.
 */
class SettingsDebug implements ServiceInterface, UtilsSettingGlobalInterface
{
	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_debug';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_debug';

	/**
	 * Filter settings is debug active key.
	 */
	public const FILTER_SETTINGS_IS_DEBUG_ACTIVE = UtilsConfig::FILTER_SETTINGS_IS_DEBUG_ACTIVE;

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'debug';

	/**
	 * Troubleshooting Use key.
	 */
	public const SETTINGS_DEBUG_USE_KEY = 'debug-use';


	/**
	 * Troubleshooting debugging key.
	 */
	public const SETTINGS_DEBUG_DEBUGGING_KEY = UtilsConfig::SETTINGS_DEBUG_DEBUGGING_KEY;
	public const SETTINGS_DEBUG_SKIP_VALIDATION_KEY = UtilsConfig::SETTINGS_DEBUG_SKIP_VALIDATION_KEY;
	public const SETTINGS_DEBUG_SKIP_RESET_KEY = UtilsConfig::SETTINGS_DEBUG_SKIP_RESET_KEY;
	public const SETTINGS_DEBUG_SKIP_CAPTCHA_KEY = UtilsConfig::SETTINGS_DEBUG_SKIP_CAPTCHA_KEY;
	public const SETTINGS_DEBUG_SKIP_FORMS_SYNC_KEY = UtilsConfig::SETTINGS_DEBUG_SKIP_FORMS_SYNC_KEY;
	public const SETTINGS_DEBUG_SKIP_CACHE_KEY = UtilsConfig::SETTINGS_DEBUG_SKIP_CACHE_KEY;
	public const SETTINGS_DEBUG_DEVELOPER_MODE_KEY = UtilsConfig::SETTINGS_DEBUG_DEVELOPER_MODE_KEY;
	public const SETTINGS_DEBUG_QM_LOG = UtilsConfig::SETTINGS_DEBUG_QM_LOG;
	public const SETTINGS_DEBUG_FORCE_DISABLED_FIELDS = UtilsConfig::SETTINGS_DEBUG_FORCE_DISABLED_FIELDS;

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
		\add_filter(self::FILTER_SETTINGS_IS_DEBUG_ACTIVE, [$this, 'isDebugActive']);
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_DEBUG_USE_KEY, self::SETTINGS_DEBUG_USE_KEY);

		if (!$isUsed) {
			return false;
		}

		return true;
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_DEBUG_USE_KEY, self::SETTINGS_DEBUG_USE_KEY)) {
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'intro',
				'introSubtitle' => \__('These options can break your forms.<br /> Use with caution!', 'eightshift-forms'),
				'introIsHighlighted' => true,
				'introIsHighlightedImportant' => true,
			],
			[
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-card',
				'tabContent' => [
					[
						'component' => 'checkboxes',
						'checkboxesFieldLabel' => '',
						'checkboxesName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_DEBUG_DEBUGGING_KEY),
						'checkboxesContent' => [
							[
								'component' => 'checkbox',
								'checkboxLabel' => \__('Bypass validation', 'eightshift-forms'),
								'checkboxIsChecked' => UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_DEBUG_SKIP_VALIDATION_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
								'checkboxValue' => self::SETTINGS_DEBUG_SKIP_VALIDATION_KEY,
								'checkboxAsToggle' => true,
								'checkboxSingleSubmit' => true,
								'checkboxHelp' => \__('Disable form validation and go directly to the integrations form submission. This way, you can debug the validation errors from the integration.', 'eightshift-forms'),
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => 'true',
							],
							[
								'component' => 'checkbox',
								'checkboxLabel' => \__('Bypass captcha', 'eightshift-forms'),
								'checkboxIsChecked' => UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_DEBUG_SKIP_CAPTCHA_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
								'checkboxValue' => self::SETTINGS_DEBUG_SKIP_CAPTCHA_KEY,
								'checkboxAsToggle' => true,
								'checkboxSingleSubmit' => true,
								'checkboxHelp' => \__('Allows sending the form without CAPTCHA validation, with the feature still enabled.', 'eightshift-forms'),
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => 'true',
							],
							[
								'component' => 'checkbox',
								'checkboxLabel' => \__("Don't clear form after submission", 'eightshift-forms'),
								'checkboxIsChecked' => UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_DEBUG_SKIP_RESET_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
								'checkboxValue' => self::SETTINGS_DEBUG_SKIP_RESET_KEY,
								'checkboxAsToggle' => true,
								'checkboxSingleSubmit' => true,
								'checkboxHelp' => \__('Disable form reset after successful submission for easier debugging.', 'eightshift-forms'),
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => 'true',
							],
							[
								'component' => 'checkbox',
								'checkboxLabel' => \__('Developer mode', 'eightshift-forms'),
								'checkboxIsChecked' => UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_DEBUG_DEVELOPER_MODE_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
								'checkboxValue' => self::SETTINGS_DEBUG_DEVELOPER_MODE_KEY,
								'checkboxAsToggle' => true,
								'checkboxSingleSubmit' => true,
								'checkboxHelp' => \__('
									Outputs multiple developers options in forms. Available outputs:<br/><br/>
									<ul>
										<li>Every listing will have ID prepended to the label.</li>
										<li>Integration API response will have a `debug` key with all the details. You can check it out using inspector network tab.</li>
										<li>On the frontend, when hovering over a form field a debug tooltip will be shown with some helpful information.</li>
									</ul>', 'eightshift-forms'),
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => 'true',
							],
							[
								'component' => 'checkbox',
								'checkboxLabel' => \__('Stop form syncing', 'eightshift-forms'),
								'checkboxIsChecked' => UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_DEBUG_SKIP_FORMS_SYNC_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
								'checkboxValue' => self::SETTINGS_DEBUG_SKIP_FORMS_SYNC_KEY,
								'checkboxAsToggle' => true,
								'checkboxSingleSubmit' => true,
								'checkboxHelp' => \__('Prevents syncing with integrations when a form is opened in edit mode.', 'eightshift-forms'),
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => 'true',
							],
							[
								'component' => 'checkbox',
								'checkboxLabel' => \__('Skip internal cache', 'eightshift-forms'),
								'checkboxIsChecked' => UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_DEBUG_SKIP_CACHE_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
								'checkboxValue' => self::SETTINGS_DEBUG_SKIP_CACHE_KEY,
								'checkboxAsToggle' => true,
								'checkboxSingleSubmit' => true,
								'checkboxHelp' => \__('Prevents storing integration data to the temporary internal cache to optimize load time and API calls. Turning on this option can cause many API calls in a short time, which may cause a temporary ban from the external integration service. Use with caution.', 'eightshift-forms'),
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => 'true',
							],
							[
								'component' => 'checkbox',
								'checkboxLabel' => \__('Output Query Monitor log', 'eightshift-forms'),
								'checkboxIsChecked' => UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_DEBUG_QM_LOG, self::SETTINGS_DEBUG_DEBUGGING_KEY),
								'checkboxValue' => self::SETTINGS_DEBUG_QM_LOG,
								'checkboxAsToggle' => true,
								'checkboxSingleSubmit' => true,
								'checkboxHelp' => \__('You can preview the output logs for internal API responses not handled by JavaScript. To use this feature, the Query Monitor plugin must be installed and active in your project.', 'eightshift-forms'),
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => 'true',
							],
							[
								'component' => 'checkbox',
								'checkboxLabel' => \__('Enable disabled fields admin overrides', 'eightshift-forms'),
								'checkboxIsChecked' => UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_DEBUG_FORCE_DISABLED_FIELDS, self::SETTINGS_DEBUG_DEBUGGING_KEY),
								'checkboxValue' => self::SETTINGS_DEBUG_FORCE_DISABLED_FIELDS,
								'checkboxAsToggle' => true,
								'checkboxSingleSubmit' => true,
								'checkboxHelp' => \__('You can use this toggle to turn off all disabled fields in the global settings. This is used to debug API keys that are stored in the global variables.', 'eightshift-forms'),
							],
						]
					],
				],
			],
		];
	}

	/**
	 * Determine if settings global is valid and debug item is active.
	 *
	 * @param string $settingKey Setting key to check.
	 *
	 * @return boolean
	 */
	public function isDebugActive(string $settingKey): bool
	{
		if (!$this->isSettingsGlobalValid()) {
			return false;
		}

		return UtilsSettingsHelper::isOptionCheckboxChecked($settingKey, self::SETTINGS_DEBUG_DEBUGGING_KEY);
	}
}
