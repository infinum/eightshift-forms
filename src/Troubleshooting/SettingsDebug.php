<?php

/**
 * Debug Settings class.
 *
 * @package EightshiftForms\Troubleshooting
 */

declare(strict_types=1);

namespace EightshiftForms\Troubleshooting;

use EightshiftForms\Settings\Settings\SettingInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsDebug class.
 */
class SettingsDebug implements ServiceInterface, SettingInterface
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
	public const SETTINGS_DEBUG_LOG_MODE_KEY = 'log-mode';
	public const SETTINGS_DEBUG_DEVELOPER_MODE_KEY = 'developer-mode';

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
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_DEBUG_USE_KEY, self::SETTINGS_DEBUG_USE_KEY);

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
		if (!$this->isCheckboxOptionChecked(self::SETTINGS_DEBUG_USE_KEY, self::SETTINGS_DEBUG_USE_KEY)) {
			return $this->getNoActiveFeatureOutput();
		}

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Debugging', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introIsHighlighted' => true,
								'introSubtitle' => \__('These options can stop your forms from working correctly, so please use them cautiously!', 'eightshift-forms'),
							],
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => $this->getSettingsName(self::SETTINGS_DEBUG_DEBUGGING_KEY),
								'checkboxesId' => $this->getSettingsName(self::SETTINGS_DEBUG_DEBUGGING_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Skip validation', 'eightshift-forms'),
										'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_DEBUG_SKIP_VALIDATION_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
										'checkboxValue' => self::SETTINGS_DEBUG_SKIP_VALIDATION_KEY,
										'checkboxAsToggle' => true,
									],
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Skip form reset after submit', 'eightshift-forms'),
										'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_DEBUG_SKIP_RESET_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
										'checkboxValue' => self::SETTINGS_DEBUG_SKIP_RESET_KEY,
										'checkboxAsToggle' => true,
									],
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Skip captcha', 'eightshift-forms'),
										'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_DEBUG_SKIP_CAPTCHA_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
										'checkboxValue' => self::SETTINGS_DEBUG_SKIP_CAPTCHA_KEY,
										'checkboxAsToggle' => true,
									],
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Output logs', 'eightshift-forms'),
										'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_DEBUG_LOG_MODE_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
										'checkboxValue' => self::SETTINGS_DEBUG_LOG_MODE_KEY,
										'checkboxAsToggle' => true,
									],
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Developer mode', 'eightshift-forms'),
										'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_DEBUG_DEVELOPER_MODE_KEY, self::SETTINGS_DEBUG_DEBUGGING_KEY),
										'checkboxValue' => self::SETTINGS_DEBUG_DEVELOPER_MODE_KEY,
										'checkboxAsToggle' => true,
									],
								]
							],
						],
					],
				]
			],
		];
	}
}
