<?php

/**
 * Geolocation Settings class.
 *
 * @package EightshiftForms\Geolocation
 */

declare(strict_types=1);

namespace EightshiftForms\Geolocation;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\Settings\SettingGlobalInterface;
use EightshiftForms\Settings\Settings\SettingsDashboard;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsGeolocation class.
 */
class SettingsGeolocation implements SettingGlobalInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

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
		if (Variables::getGeolocationUse()) {
			return true;
		}

		if (!$this->isCheckboxOptionChecked(self::SETTINGS_GEOLOCATION_USE_KEY, self::SETTINGS_GEOLOCATION_USE_KEY)) {
			return false;
		}

		// Add the ability to disable geolocation from an external source (generally used for GDPR purposes).
		$filterName = Filters::getFilterName(['geolocation', 'disable']);
		if (\has_filter($filterName) && \apply_filters($filterName, null)) {
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
		$use = Variables::getGeolocationUse();

		// Bailout if feature is not active.
		if (!$this->isCheckboxOptionChecked(self::SETTINGS_GEOLOCATION_USE_KEY, self::SETTINGS_GEOLOCATION_USE_KEY) && !$use) {
			return $this->getNoActiveFeatureOutput();
		}

		$useRocket = Variables::getGeolocationUseWpRocket();

		$outputConstants = '';

		if (Variables::getGeolocationUseWpRocket()) {
			$outputConstants .= '<br/>' . $this->getAppliedGlobalConstantOutput('ES_GEOLOCATION_USE_WP_ROCKET');
		}

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'layout',
				'layoutType' => 'layout-v-stack-card',
				'layoutContent' => [
					[
						'component' => 'intro',
						// translators: %s will be replaced with the link.
						'introSubtitle' => \sprintf(\__("
							<p>By default geolocation uses cookie to store users location and that location is matched with the country list got from the <a href='%1\$s' target='_blank' rel='noopener noreferrer'>GeoLite2</a> by MaxMind database.</p>
							<p>With every release we update that database but you can also provide your own database by using our filters. You can find more details <a href='%2\$s' rel='noopener noreferrer' target='_blank'>here</a>.</p>
						", 'eightshift-forms'), 'https://www.maxmind.com', 'https://eightshift.com/forms/features/geolocation'),
					],
				],
			],
			(\is_plugin_active('wp-rocket/wp-rocket.php') && !Variables::getGeolocationUseWpRocket()) ? [
				'component' => 'intro',
				// translators: %s will be replaced with the link.
				'introSubtitle' => \sprintf(\__('
					<b>Geolocation is not working due to WP Rocket plugin</b>
					<p>
						We have detected that you are using the WP Rocket plugin.
						Please turn on the WP Rocket feature in the global settings <a href="%s" rel="noopener noreferrer">dashboard</a> for proper geolocation functionality.
					</p>
				', 'eightshift-forms'), Helper::getSettingsGlobalPageUrl(SettingsDashboard::SETTINGS_TYPE_KEY)),
				'introIsHighlighted' => true,
				'introIsHighlightedImportant' => true,
			] : [],
			(\is_plugin_active('cloudflare/cloudflare.php') && !Variables::getGeolocationUseCloudflare()) ? [
				'component' => 'intro',
				// translators: %s will be replaced with the link.
				'introSubtitle' => \sprintf(\__('
					<b>Geolocation is not working due to Cloudflare plugin</b>
					<p>
						We have detected that you are using the Cloudflare plugin.
						Please turn on the Cloudflare feature in the global settings <a href="%s" rel="noopener noreferrer">dashboard</a> for proper geolocation functionality.
					</p>
				', 'eightshift-forms'), Helper::getSettingsGlobalPageUrl(SettingsDashboard::SETTINGS_TYPE_KEY)),
				'introIsHighlighted' => true,
				'introIsHighlightedImportant' => true,
			] : [],
		];
	}
}
