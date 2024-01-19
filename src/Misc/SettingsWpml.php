<?php

/**
 * WPML Settings class.
 *
 * @package EightshiftForms\Misc
 */

declare(strict_types=1);

namespace EightshiftForms\Misc;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsWpml class.
 */
class SettingsWpml implements UtilsSettingGlobalInterface, ServiceInterface
{
	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_wpml';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_wpml';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'wpml';

	/**
	 * Wpml use key.
	 */
	public const SETTINGS_WPML_USE_KEY = 'wpml-use';

	/**
	 * Register all the hooks.
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
		\add_filter(UtilsHooksHelper::getFilterName(['general', 'locale']), [$this, 'getWpmlLocale']);
	}

	/**
	 * Set locale for WPML.
	 *
	 * @return array<string, mixed>
	 */
	public function getWpmlLocale(): array
	{
		if (!$this->isSettingsGlobalValid()) {
			return [];
		}

		$current = \apply_filters('wpml_current_language', null);

		return [
			'default' => \apply_filters('wpml_default_language', null),
			'current' => $current === 'all' ? '' : $current,
		];
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_WPML_USE_KEY, self::SETTINGS_WPML_USE_KEY);

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
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_WPML_USE_KEY, self::SETTINGS_WPML_USE_KEY)) {
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			UtilsSettingsOutputHelper::getMiscDisclaimer(),
			[
				'component' => 'intro',
				'introSubtitle' => \__('In order for Eightshift Forms to work correctly with WPML, you must enable translations in the WPML settings.', 'eightshift-forms'),
				'introIsHighlighted' => true,
				'introIsHighlightedImportant' => true,
			],
			[
				'component' => 'intro',
				'introTitle' => \__('Features affected by WPML are:', 'eightshift-forms'),
			],
			[
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-card',
				'layoutContent' => [
					[
						'component' => 'intro',
						'introTitle' => \__('Forms listing', 'eightshift-forms'),
						'introSubtitle' => \__("By default, we use the `get_locale()` function to retrieve the default language of your project. Once the WPML plugin is activated, we assign a new language tag to each setting and display forms only in the specific language.", 'eightshift-forms'),
					],
				],
			],
			[
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-card',
				'layoutContent' => [
					[
						'component' => 'intro',
						'introTitle' => \__('Forms selector', 'eightshift-forms'),
						'introSubtitle' => \__("When selecting forms in your forms picker, you will only see forms available in your language.", 'eightshift-forms'),
					],
				],
			],
			[
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-card',
				'layoutContent' => [
					[
						'component' => 'intro',
						'introTitle' => \__('Forms settings', 'eightshift-forms'),
						'introSubtitle' => \__("We will make each forms settings language specific.", 'eightshift-forms'),
					],
				],
			],
			[
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-card',
				'layoutContent' => [
					[
						'component' => 'intro',
						'introTitle' => \__('Global settings', 'eightshift-forms'),
						'introSubtitle' => \__("Global settings will be language specific, except for API keys, tokens and etc.", 'eightshift-forms'),
					],
				],
			],
		];
	}
}
