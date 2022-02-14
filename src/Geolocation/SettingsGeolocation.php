<?php

/**
 * Geolocation Settings class.
 *
 * @package EightshiftForms\Geolocation
 */

declare(strict_types=1);

namespace EightshiftForms\Geolocation;

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
		return $this->isCheckboxOptionChecked(self::SETTINGS_GEOLOCATION_USE_KEY, self::SETTINGS_GEOLOCATION_USE_KEY);
	}

	/**
	 * Get Settings sidebar data.
	 *
	 * @return array<string, mixed>
	 */
	public function getSettingsSidebar(): array
	{
		return [
			'label' => __('GeoLocation', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => '<svg width="30" height="30" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient x1="11.104%" y1="70.178%" x2="88.905%" y2="29.822%" id="a"><stop stop-color="red" offset="2%"/><stop stop-color="#9E005D" offset="100%"/></linearGradient></defs><g fill-rule="nonzero" fill="none"><path d="M20.688 0H9.315C8.02 0 6.821.69 6.173 1.813l-5.686 9.85a3.62 3.62 0 0 0 0 3.625l5.686 9.852a3.629 3.629 0 0 0 3.142 1.813h11.373a3.626 3.626 0 0 0 3.14-1.813l5.685-9.852a3.62 3.62 0 0 0 0-3.626l-5.686-9.836A3.626 3.626 0 0 0 20.687 0Z" fill="url(#a)" transform="translate(0 2)"/><path d="M14.999 7.936a5.439 5.439 0 0 1 4.9 7.805c-.203.423-3.109 5.051-4.323 6.981a.62.62 0 0 1-.532.295.638.638 0 0 1-.532-.295c-1.25-1.938-4.219-6.577-4.423-7.005a5.442 5.442 0 0 1 4.91-7.78m0-1.101a6.54 6.54 0 0 0-5.905 9.357c.064.133.266.532 4.485 7.124.32.499.873.8 1.465.798a1.72 1.72 0 0 0 1.465-.798c4.136-6.584 4.314-6.956 4.383-7.097A6.542 6.542 0 0 0 15 6.836Z" fill="#FFF"/></g></svg>',
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
		return [
			[
				'component' => 'intro',
				'introTitle' => __('GeoLocation settings', 'eightshift-forms'),
				'introSubtitle' => __('See all the GeoLocations where your block is assigned in one place.', 'eightshift-forms'),
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => __('Check options to use', 'eightshift-forms'),
				'checkboxesFieldHelp' => __('Select integrations you want to use in your form.', 'eightshift-forms'),
				'checkboxesName' => $this->getSettingsName(self::SETTINGS_GEOLOCATION_USE_KEY),
				'checkboxesId' => $this->getSettingsName(self::SETTINGS_GEOLOCATION_USE_KEY),
				'checkboxesIsRequired' => true,
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('Use Google reCaptcha', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_GEOLOCATION_USE_KEY, self::SETTINGS_GEOLOCATION_USE_KEY),
						'checkboxValue' => self::SETTINGS_GEOLOCATION_USE_KEY,
						'checkboxSingleSubmit' => true,
					]
				]
			],
		];
	}
}
