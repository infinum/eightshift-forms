<?php

/**
 * Debug Settings class.
 *
 * @package EightshiftForms\Troubleshooting
 */

declare(strict_types=1);

namespace EightshiftForms\Troubleshooting;

use EightshiftForms\Settings\Settings\SettingGlobalInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsDebug class.
 */
class SettingsDebug implements ServiceInterface, SettingGlobalInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_debug';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_debug';

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
	public const SETTINGS_DEBUG_DEBUGGING_KEY = 'troubleshooting-debugging';
	public const SETTINGS_DEBUG_SKIP_VALIDATION_KEY = 'skip-validation';
	public const SETTINGS_DEBUG_SKIP_RESET_KEY = 'skip-reset';
	public const SETTINGS_DEBUG_SKIP_CAPTCHA_KEY = 'skip-captcha';
	public const SETTINGS_DEBUG_DEVELOPER_MODE_KEY = 'developer-mode';
	public const SETTINGS_DEBUG_SKIP_FORMS_SYNC_KEY = 'skip-forms-sync';
	public const SETTINGS_DEBUG_SKIP_CACHE_KEY = 'skip-cache';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = $this->isOptionCheckboxChecked(self::SETTINGS_DEBUG_USE_KEY, self::SETTINGS_DEBUG_USE_KEY);

		if (!$isUsed) {
			return false;
		}

		return true;
	}

	/**
	 * Get Form settings data array.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsData(string $formId): array
	{
		return [];
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		if (!$this->isOptionCheckboxChecked(self::SETTINGS_DEBUG_USE_KEY, self::SETTINGS_DEBUG_USE_KEY)) {
			return $this->getSettingOutputNoActiveFeature();
		}

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
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
						'checkboxesName' => $this->getOptionName(self::SETTINGS_DEBUG_DEBUGGING_KEY),
						'checkboxesContent' => [
							[
								'component' => 'checkbox',
								'checkboxLabel' => \__('Bypass validation', 'eightshift-forms'),
								'checkboxIsChecked' => $this->isOptionCheckboxChecked(self::SETTINGS_DEBUG_SKIP_VALIDATION_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
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
								'checkboxIsChecked' => $this->isOptionCheckboxChecked(self::SETTINGS_DEBUG_SKIP_CAPTCHA_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
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
								'checkboxIsChecked' => $this->isOptionCheckboxChecked(self::SETTINGS_DEBUG_SKIP_RESET_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
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
								'checkboxIsChecked' => $this->isOptionCheckboxChecked(self::SETTINGS_DEBUG_DEVELOPER_MODE_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
								'checkboxValue' => self::SETTINGS_DEBUG_DEVELOPER_MODE_KEY,
								'checkboxAsToggle' => true,
								'checkboxSingleSubmit' => true,
								'checkboxHelp' => \__('
									Outputs multiple developers options in forms. Available outputs:<br/><br/>
									<ul>
										<li>Every listing will have ID prepended to the label.</li>
										<li>Integration api response will have a `debug` key with all the details. You can check it out using inspector network tab.</li>
									</ul>', 'eightshift-forms'),
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => 'true',
							],
							[
								'component' => 'checkbox',
								'checkboxLabel' => \__('Stop form syncing', 'eightshift-forms'),
								'checkboxIsChecked' => $this->isOptionCheckboxChecked(self::SETTINGS_DEBUG_SKIP_FORMS_SYNC_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
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
								'checkboxIsChecked' => $this->isOptionCheckboxChecked(self::SETTINGS_DEBUG_SKIP_CACHE_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
								'checkboxValue' => self::SETTINGS_DEBUG_SKIP_CACHE_KEY,
								'checkboxAsToggle' => true,
								'checkboxSingleSubmit' => true,
								'checkboxHelp' => \__('Prevents storing integration data to the temporary internal cache to optimize load time and API calls. Turning on this option can cause many API calls in a short time, which may cause a temporary ban from the external integration service. Use with caution.', 'eightshift-forms'),
							],
						]
					],
				],
			],
		];
	}
}
