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
use EightshiftForms\Settings\Settings\SettingInterface;
use EightshiftForms\Settings\Settings\SettingsDocumentation;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsGeolocation class.
 */
class SettingsGeolocation implements SettingInterface, ServiceInterface
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
		$filterName = Filters::getGeolocationFilterName('disable');
		if (\has_filter($filterName) && \apply_filters($filterName, null)) {
			return false;
		}

		return true;
	}

	/**
	 * Get Form settings data array
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
		$use = Variables::getGeolocationUse();

		// Bailout if feature is not active.
		if (!$this->isCheckboxOptionChecked(self::SETTINGS_GEOLOCATION_USE_KEY, self::SETTINGS_GEOLOCATION_USE_KEY) && !$use) {
			return $this->getNoActiveFeatureOutput();
		}

		$useRocket = Variables::getGeolocationUseWpRocketAdvancedCache();

		$outputConstants = '';

		if ($use) {
			$outputConstants .= $this->getAppliedGlobalConstantOutput('ES_GEOLOCATION_USE');
		}

		if (Variables::getGeolocationUseWpRocketAdvancedCache()) {
			$outputConstants .= '<br/>' . $this->getAppliedGlobalConstantOutput('ES_GEOLOCATION_USE_WP_ROCKET_ADVANCED_CACHE');
		}

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'intro',
				// translators: %s will be replaced with the link.
				'introSubtitle' => \sprintf(\__('This product includes GeoLite2 data created by MaxMind, available on this <a href="%s" target="_blank" rel="noopener noreferrer">link</a>.', 'eightshift-forms'), 'https://www.maxmind.com'),
			],
			[
				'component' => 'intro',
				'introIsHighlighted' => true,
				'introTitle' => \__('Caching'),
				'introTitleSize' => 'medium',
				// translators: %s will be replaced with the link.
				'introSubtitle' => \sprintf(\__('Please keep in mind that Geolocation will not work correctly if you have caching on the user\'s side of your website such as WP Rocket, Cloudflare, etc. If you are using caching, please refer to our <a href="%s" target="_blank" rel="noopener noreferrer">documentation</a> for more details.', 'eightshift-forms'), Helper::getSettingsGlobalPageUrl(SettingsDocumentation::SETTINGS_TYPE_KEY)),
			],
			($use || $useRocket) ? [
				'component' => 'intro',
				'introIsHighlighted' => true,
				'introIsHighlightedImportant' => true,
				'introTitleSize' => 'medium',
				// translators: %s will be replaced with the link.
				'introSubtitle' => $outputConstants,
			] : [],
		];
	}
}
