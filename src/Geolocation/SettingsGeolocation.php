<?php

/**
 * Geolocation Settings class.
 *
 * @package EightshiftForms\Geolocation
 */

declare(strict_types=1);

namespace EightshiftForms\Geolocation;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\Settings\SettingsAll;
use EightshiftForms\Settings\Settings\SettingsDataInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsGeolocation class.
 */
class SettingsGeolocation implements SettingsDataInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings sidebar key.
	 */
	public const FILTER_SETTINGS_SIDEBAR_NAME = 'es_forms_settings_sidebar_geolocation';

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
		\add_filter(self::FILTER_SETTINGS_SIDEBAR_NAME, [$this, 'getSettingsSidebar']);
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
	 * Get Settings sidebar data.
	 *
	 * @return array<string, mixed>
	 */
	public function getSettingsSidebar(): array
	{
		return [
			'label' => \__('Geolocation', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => Filters::ALL[self::SETTINGS_TYPE_KEY]['icon'],
			'type' => SettingsAll::SETTINGS_SIEDBAR_TYPE_GENERAL,
		];
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
		$useRocket = Variables::getGeolocationUseWpRocketAdvancedCache();

		$outputHelp = '';

		if ($use) {
			$outputHelp .= \__('Hook "ES_GEOLOCATION_USE" is active.<br />', 'eightshift-forms');
		}

		if ($useRocket) {
			$outputHelp .= \__('Hook "ES_GEOLOCATION_USE_WP_ROCKET_ADVANCED_CACHE" is active.', 'eightshift-forms');
		}

		return [
			[
				'component' => 'intro',
				'introTitle' => \__('Geolocation', 'eightshift-forms'),
				// translators: %s provides the link to external source.
				'introSubtitle' => \sprintf(\__('Allows conditionally rendering different forms based on the user\'s location. Uses a local geolocation API. Consult documentation for more info. <br />This product includes GeoLite2 data created by MaxMind, available from <a href="%1$s">%1$s</a>.', 'eightshift-forms'), 'https://www.maxmind.com'),
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => '',
				'checkboxesFieldHelp' => $outputHelp,
				'checkboxesName' => $this->getSettingsName(self::SETTINGS_GEOLOCATION_USE_KEY),
				'checkboxesId' => $this->getSettingsName(self::SETTINGS_GEOLOCATION_USE_KEY),
				'checkboxesIsRequired' => true,
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => \__('Use geolocation', 'eightshift-forms'),
						'checkboxIsChecked' => $use ? $use : $this->isCheckboxOptionChecked(self::SETTINGS_GEOLOCATION_USE_KEY, self::SETTINGS_GEOLOCATION_USE_KEY),
						'checkboxIsDisabled' => $use,
						'checkboxValue' => self::SETTINGS_GEOLOCATION_USE_KEY,
						'checkboxSingleSubmit' => true,
					]
				]
			],
		];
	}
}
