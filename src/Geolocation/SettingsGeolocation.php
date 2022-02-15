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
			'label' => __('Geolocation', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><ellipse cx="10" cy="18.625" rx="2.5" ry=".625" fill="#29A3A3" fill-opacity=".12"/><path d="m10 18-.53.53a.75.75 0 0 0 1.06 0L10 18zm4.75-10.5c0 2.261-1.26 4.726-2.618 6.7a27.012 27.012 0 0 1-2.442 3.04 14.893 14.893 0 0 1-.208.218l-.01.01-.002.002.53.53.53.53h.001l.001-.002a2.19 2.19 0 0 0 .018-.018l.05-.05.183-.193a28.473 28.473 0 0 0 2.585-3.217c1.393-2.026 2.882-4.811 2.882-7.55h-1.5zM10 18l.53-.53-.002-.002-.01-.01a8.665 8.665 0 0 1-.208-.217 27 27 0 0 1-2.442-3.04C6.511 12.225 5.25 9.76 5.25 7.5h-1.5c0 2.739 1.49 5.524 2.882 7.55a28.494 28.494 0 0 0 2.585 3.217 16.44 16.44 0 0 0 .233.244l.014.013.004.004v.002h.001L10 18zM5.25 7.5A4.75 4.75 0 0 1 10 2.75v-1.5A6.25 6.25 0 0 0 3.75 7.5h1.5zM10 2.75a4.75 4.75 0 0 1 4.75 4.75h1.5A6.25 6.25 0 0 0 10 1.25v1.5z" fill="#29A3A3"/><circle cx="10" cy="7.5" r="1.5" fill="#29A3A3" fill-opacity=".3"/></svg>',
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
				'introTitle' => __('Geolocation', 'eightshift-forms'),
				'introSubtitle' => __('Allows conditionally rendering different forms based on the user\'s location. Uses a local geolocation API. Consult documentation for more info.', 'eightshift-forms'),
			],
			[
				'component' => 'checkboxes',
				'checkboxesFieldLabel' => '',
				'checkboxesName' => $this->getSettingsName(self::SETTINGS_GEOLOCATION_USE_KEY),
				'checkboxesId' => $this->getSettingsName(self::SETTINGS_GEOLOCATION_USE_KEY),
				'checkboxesIsRequired' => true,
				'checkboxesContent' => [
					[
						'component' => 'checkbox',
						'checkboxLabel' => __('Use geolocation', 'eightshift-forms'),
						'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_GEOLOCATION_USE_KEY, self::SETTINGS_GEOLOCATION_USE_KEY),
						'checkboxValue' => self::SETTINGS_GEOLOCATION_USE_KEY,
						'checkboxSingleSubmit' => true,
					]
				]
			],
		];
	}
}
