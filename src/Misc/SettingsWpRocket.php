<?php

/**
 * WP Rocket Settings class.
 *
 * @package EightshiftForms\Misc
 */

declare(strict_types=1);

namespace EightshiftForms\Misc;

use EightshiftForms\Settings\Settings\SettingGlobalInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsWpRocket class.
 */
class SettingsWpRocket implements SettingGlobalInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_wprocket';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_wprocket';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'wprocket';

	/**
	 * WpRocket use key.
	 */
	public const SETTINGS_WPROCKET_USE_KEY = 'wprocket-use';

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
		$isUsed = $this->isCheckboxOptionChecked(self::SETTINGS_WPROCKET_USE_KEY, self::SETTINGS_WPROCKET_USE_KEY);

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
		if (!$this->isCheckboxOptionChecked(self::SETTINGS_WPROCKET_USE_KEY, self::SETTINGS_WPROCKET_USE_KEY)) {
			return $this->getNoActiveFeatureOutput();
		}

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-card',
				'layoutContent' => [
					[
						'component' => 'intro',
						'introTitle' => \__('Disclamer', 'eightshift-forms'),
						'introSubtitle' => \__("Eightshift Forms doesn't configure the WP Rocket app or any other third-party tools. However, enabling this feature adds necessary configurations in the backend for everything to function correctly.", 'eightshift-forms'),
					],
				],
			],
			[
				'component' => 'intro',
				'introTitle' => \__('Features affected by WP Rocket are:', 'eightshift-forms'),
			],
			[
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-card',
				'layoutContent' => [
					[
						'component' => 'intro',
						'introTitle' => \__('Geolocation', 'eightshift-forms'),
						'introSubtitle' => \sprintf(\__("
							By default geolocation uses cookie to store users location.
							With WP Rocket enabled, geolocation will make sure that the cookie is stored on the server before requested page is procesed by the WP Rocket.
							<p>
								If you have WP Rocket plugin instaled we will add geolocation cookie to the dynamic cookies list that will provide different cached pages based on the geolocation cookie.
								You can find more details on WP Rocket and geolocations <a href='$1' rel='noopener noreferrer' target='_blank'>here</a>.
							</p>
						", 'eightshift-forms'), 'https://eightshift.com/forms/features/geolocation'),
					],
					[
						'component' => 'intro',
						'introIsHighlighted' => true,
						'introIsHighlightedImportant' => true,
						'introSubtitle' => \sprintf(\__("
							In order for geolocation to work correctly make sure you deactivate and then activate WP Rocket plugin after turning on this feature.
						", 'eightshift-forms'), 'https://eightshift.com/forms/features/geolocation'),
					],
				],
			],
		];
	}
}
