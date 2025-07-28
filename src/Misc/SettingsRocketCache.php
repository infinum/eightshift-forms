<?php

/**
 * Rocket Cache Settings class.
 *
 * @package EightshiftForms\Misc
 */

declare(strict_types=1);

namespace EightshiftForms\Misc;

use EightshiftForms\Helpers\SettingsOutputHelpers;
use EightshiftForms\Settings\SettingGlobalInterface;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsRocketCache class.
 */
class SettingsRocketCache implements SettingGlobalInterface, ServiceInterface
{
	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_rocket_cache';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_rocket_cache';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'rocket-cache';

	/**
	 * Rocket cache use key.
	 */
	public const SETTINGS_ROCKET_CACHE_USE_KEY = 'rocket-cache-use';

	/**
	 * Register all the hooks.
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
		$isUsed = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_ROCKET_CACHE_USE_KEY, self::SETTINGS_ROCKET_CACHE_USE_KEY);

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
		if (!SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_ROCKET_CACHE_USE_KEY, self::SETTINGS_ROCKET_CACHE_USE_KEY)) {
			return SettingsOutputHelpers::getNoActiveFeature();
		}

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
			SettingsOutputHelpers::getMiscDisclaimer(\__('WP Rocket Cache', 'eightshift-forms')),
			[
				'component' => 'intro',
				'introTitle' => \__('Features affected by WP Rocket cache are:', 'eightshift-forms'),
			],
			[
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-card',
				'layoutContent' => [
					[
						'component' => 'intro',
						'introTitle' => \__('Forms cache', 'eightshift-forms'),
						'introSubtitle' => \__('When you clear forms cache, you will also clear WP Rocket cache.', 'eightshift-forms'),
					],
				],
			],
		];
	}
}
