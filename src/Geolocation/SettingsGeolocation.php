<?php

/**
 * Geolocation Settings class.
 *
 * @package EightshiftForms\Geolocation
 */

declare(strict_types=1);

namespace EightshiftForms\Geolocation;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftForms\Dashboard\SettingsDashboard;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftForms\Misc\SettingsCloudflare;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsGeolocation class.
 */
class SettingsGeolocation implements UtilsSettingGlobalInterface, ServiceInterface
{
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_geolocation';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_geolocation';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'geolocation';

	/**
	 * Geolocation Use key.
	 */
	public const SETTINGS_GEOLOCATION_USE_KEY = 'geolocation-use';

	/**
	 * Geolocation Cookieless Use key.
	 */
	public const SETTINGS_GEOLOCATION_COOKIELESS_USE_KEY = 'geolocation-cookieless-use';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_GEOLOCATION_USE_KEY, self::SETTINGS_GEOLOCATION_USE_KEY)) {
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
		// Bailout if feature is not active.
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_GEOLOCATION_USE_KEY, self::SETTINGS_GEOLOCATION_USE_KEY)) {
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		$useCookieless = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_GEOLOCATION_COOKIELESS_USE_KEY, self::SETTINGS_GEOLOCATION_COOKIELESS_USE_KEY);

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-card',
				'layoutContent' => [
					[
						'component' => 'intro',
						// translators: %s will be replaced with the link.
						'introSubtitle' => \sprintf(\__("
							<p>By default geolocation uses <a href='%1\$s' target='_blank' rel='noopener noreferrer'>GeoLite2</a> MaxMind ID database</a> to get users location.</p>
							<p>With every release we update that database but you can also provide your own database by using our filters. You can find more details <a href='%2\$s' rel='noopener noreferrer' target='_blank'>here</a>.</p>
						", 'eightshift-forms'), 'https://www.maxmind.com', 'https://eightshift.com/forms/features/geolocation'),
					],
					[
						'component' => 'divider',
						'dividerExtraVSpacing' => true,
					],
					[
						'component' => 'checkboxes',
						'checkboxesFieldLabel' => '',
						'checkboxesName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_GEOLOCATION_COOKIELESS_USE_KEY),
						'checkboxesFieldHelp' => \__("By default we store a cookie for faster geo-location detection. By enabling this feature, forms will not store the geo-location cookie but keep in mind that this may increase geo-location detection time.", 'eightshift-forms'),
						'checkboxesContent' => [
							[
								'component' => 'checkbox',
								'checkboxLabel' => \__('Use cookieless geo-location detection', 'eightshift-forms'),
								'checkboxIsChecked' => $useCookieless,
								'checkboxValue' => self::SETTINGS_GEOLOCATION_COOKIELESS_USE_KEY,
								'checkboxSingleSubmit' => true,
								'checkboxAsToggle' => true,
							]
						]
					],
				],
			],
			(\is_plugin_active('cloudflare/cloudflare.php') && !UtilsSettingsHelper::isOptionCheckboxChecked(SettingsCloudflare::SETTINGS_CLOUDFLARE_USE_KEY, SettingsCloudflare::SETTINGS_CLOUDFLARE_USE_KEY)) ? [
				'component' => 'intro',
				// translators: %s will be replaced with the link.
				'introSubtitle' => \sprintf(\__('
					<b>Geolocation is not working due to Cloudflare plugin</b>
					<p>
						We have detected that you are using the Cloudflare plugin.
						Please turn on the Cloudflare feature in the global settings <a href="%s" rel="noopener noreferrer">dashboard</a> for proper geolocation functionality.
					</p>
				', 'eightshift-forms'), UtilsGeneralHelper::getSettingsGlobalPageUrl(SettingsDashboard::SETTINGS_TYPE_KEY)),
				'introIsHighlighted' => true,
				'introIsHighlightedImportant' => true,
			] : [],
		];
	}
}
