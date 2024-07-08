<?php

/**
 * Custom data block settings class.
 *
 * @package EightshiftForms\Blocks
 */

declare(strict_types=1);

namespace EightshiftForms\Blocks;

use EightshiftForms\Countries\CountriesInterface;
use EightshiftForms\Geolocation\GeolocationInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsBlocks class.
 */
class SettingsBlocks implements UtilsSettingGlobalInterface, UtilsSettingInterface, ServiceInterface
{
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_blocks';

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_blocks';

	/**
	 * Filter country dataset value key.
	 */
	public const FILTER_SETTINGS_BLOCK_COUNTRY_DATASET_VALUE_NAME = 'es_forms_block_country_dataset_value';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'blocks';

	/**
	 * Country keys.
	 */
	public const SETTINGS_BLOCK_COUNTRY_OVERRIDE_GLOBAL_SETTINGS_KEY = 'block-country-override-global-settings';
	public const SETTINGS_BLOCK_COUNTRY_FALLBACK_VALUE_KEY = 'us';
	public const SETTINGS_BLOCK_COUNTRY_DATA_SET_KEY = 'block-country-data-set';
	public const SETTINGS_BLOCK_COUNTRY_DATA_SET_GLOBAL_KEY = self::SETTINGS_BLOCK_COUNTRY_DATA_SET_KEY . '-global';

	/**
	 * Phone keys.
	 */
	public const SETTINGS_BLOCK_PHONE_OVERRIDE_GLOBAL_SETTINGS_KEY = 'block-phone-override-global-settings';
	public const SETTINGS_BLOCK_PHONE_DATA_SET_KEY = 'block-phone-data-set';
	public const SETTINGS_BLOCK_PHONE_DATA_SET_GLOBAL_KEY = self::SETTINGS_BLOCK_PHONE_DATA_SET_KEY . '-global';
	public const SETTINGS_BLOCK_PHONE_DISABLE_SYNC_KEY = 'block-phone-disable-sync';
	public const SETTINGS_BLOCK_PHONE_DISABLE_PICKER_KEY = 'block-phone-disable-picker';
	public const SETTINGS_BLOCK_PHONE_USE_COUNTRY_DATA_KEY = 'block-phone-use-country-data';
	public const SETTINGS_BLOCK_PHONE_USE_COUNTRY_DATA_GLOBAL_KEY = self::SETTINGS_BLOCK_PHONE_USE_COUNTRY_DATA_KEY . '-global';

	/**
	 * Instance variable of geolocation data.
	 *
	 * @var GeolocationInterface
	 */
	private GeolocationInterface $geolocation;

	/**
	 * Instance variable of countries data.
	 *
	 * @var CountriesInterface
	 */
	private CountriesInterface $countries;

	/**
	 * Create a new admin instance.
	 *
	 * @param GeolocationInterface $geolocation Inject geolocation which holds data about for storing to geolocation.
	 * @param CountriesInterface $countries Inject countries which holds data about for storing to countries.
	 */
	public function __construct(
		GeolocationInterface $geolocation,
		CountriesInterface $countries
	) {
		$this->geolocation = $geolocation;
		$this->countries = $countries;
	}

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_BLOCK_COUNTRY_DATASET_VALUE_NAME, [$this, 'getCountryDatasetValue'], 9999);
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
		$overrideGlobalSettingsCountry = UtilsSettingsHelper::isSettingCheckboxChecked(self::SETTINGS_BLOCK_COUNTRY_OVERRIDE_GLOBAL_SETTINGS_KEY, self::SETTINGS_BLOCK_COUNTRY_OVERRIDE_GLOBAL_SETTINGS_KEY, $formId);
		$overrideGlobalSettingsPhone = UtilsSettingsHelper::isSettingCheckboxChecked(self::SETTINGS_BLOCK_PHONE_OVERRIDE_GLOBAL_SETTINGS_KEY, self::SETTINGS_BLOCK_PHONE_OVERRIDE_GLOBAL_SETTINGS_KEY, $formId);

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Country', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_BLOCK_COUNTRY_OVERRIDE_GLOBAL_SETTINGS_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Override global settings', 'eightshift-forms'),
										'checkboxIsChecked' => $overrideGlobalSettingsCountry,
										'checkboxValue' => self::SETTINGS_BLOCK_COUNTRY_OVERRIDE_GLOBAL_SETTINGS_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
										'checkboxAsToggleSize' => 'medium',
									],
								],
							],
							...($overrideGlobalSettingsCountry ? [
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => 'true',
								],
								[
									'component' => 'select',
									'selectFieldLabel' => \__('Dataset used', 'eightshift-forms'),
									'selectName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_BLOCK_COUNTRY_DATA_SET_KEY),
									'selectContent' => $this->getCountrySettingsList(
										UtilsSettingsHelper::getSettingValueWithFallback(self::SETTINGS_BLOCK_COUNTRY_DATA_SET_KEY, self::SETTINGS_BLOCK_COUNTRY_DATA_SET_GLOBAL_KEY, 'default', $formId),
										'items'
									),
								],
							] : []),
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Phone', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_BLOCK_PHONE_OVERRIDE_GLOBAL_SETTINGS_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Override global settings', 'eightshift-forms'),
										'checkboxIsChecked' => $overrideGlobalSettingsPhone,
										'checkboxValue' => self::SETTINGS_BLOCK_PHONE_OVERRIDE_GLOBAL_SETTINGS_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
										'checkboxAsToggleSize' => 'medium',
									],
								],
							],
							...($overrideGlobalSettingsPhone ? [
								[
									'component' => 'select',
									'selectFieldLabel' => \__('Dataset used', 'eightshift-forms'),
									'selectName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_BLOCK_PHONE_DATA_SET_KEY),
									'selectContent' => $this->getCountrySettingsList(
										UtilsSettingsHelper::getSettingValueWithFallback(self::SETTINGS_BLOCK_PHONE_DATA_SET_KEY, self::SETTINGS_BLOCK_PHONE_DATA_SET_GLOBAL_KEY, 'default', $formId),
										'items'
									),
								],
							] : []),
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => 'true',
							],
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => UtilsSettingsHelper::getSettingName(self::SETTINGS_BLOCK_PHONE_DISABLE_SYNC_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Disable phone/country sync on change', 'eightshift-forms'),
										'checkboxIsChecked' => UtilsSettingsHelper::isSettingCheckboxChecked(self::SETTINGS_BLOCK_PHONE_DISABLE_SYNC_KEY, self::SETTINGS_BLOCK_PHONE_DISABLE_SYNC_KEY, $formId),
										'checkboxValue' => self::SETTINGS_BLOCK_PHONE_DISABLE_SYNC_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
										'checkboxAsToggleSize' => 'medium',
									],
								],
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		$disablePhoneCountryPicker = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_BLOCK_PHONE_DISABLE_PICKER_KEY, self::SETTINGS_BLOCK_PHONE_DISABLE_PICKER_KEY);

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Country', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'select',
								'selectFieldLabel' => \__('Dataset used', 'eightshift-forms'),
								'selectName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_BLOCK_COUNTRY_DATA_SET_GLOBAL_KEY),
								'selectContent' => $this->getCountrySettingsList(
									UtilsSettingsHelper::getOptionValueWithFallback(self::SETTINGS_BLOCK_COUNTRY_DATA_SET_GLOBAL_KEY, 'default'),
									'items'
								),
							],
							[
								'component' => 'divider',
								'dividerExtraVSpacing' => true,
							],
							[
								'component' => 'textarea',
								'textareaFieldLabel' => \__('Countries in dataset', 'eightshift-forms'),
								'selectFieldHelp' => \__('This is the list of our default countries name, iso code and call number prefix.', 'eightshift-forms'),
								'textareaIsReadOnly' => true,
								'textareaIsPreventSubmit' => true,
								'textareaName' => 'country',
								'textareaValue' => \wp_json_encode($this->countries->getCountriesDataSet(), \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE),
								'textareaSize' => 'huge',
								'textareaLimitHeight' => true,
							],
						],
					],
					[
						'component' => 'tab',
						'tabLabel' => \__('Phone', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'checkboxes',
								'checkboxesFieldLabel' => '',
								'checkboxesName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_BLOCK_PHONE_DISABLE_PICKER_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Disable area code picker', 'eightshift-forms'),
										'checkboxIsChecked' => $disablePhoneCountryPicker,
										'checkboxValue' => self::SETTINGS_BLOCK_PHONE_DISABLE_PICKER_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
									],
								],
							],
							...(!$disablePhoneCountryPicker ? [
								[
									'component' => 'divider',
									'dividerExtraVSpacing' => true,
								],
								[
									'component' => 'select',
									'selectFieldLabel' => \__('Dataset used', 'eightshift-forms'),
									'selectName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_BLOCK_PHONE_DATA_SET_GLOBAL_KEY),
									'selectContent' => $this->getCountrySettingsList(
										UtilsSettingsHelper::getOptionValueWithFallback(self::SETTINGS_BLOCK_PHONE_DATA_SET_GLOBAL_KEY, 'default'),
										'items'
									),
								],
							] : []),
						],
					],
				],
			],
		];
	}

	/**
	 * Get block country and phone settings output.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<string, mixed>
	 */
	public function getCountryDatasetValue(string $formId): array
	{
		if (UtilsSettingsHelper::isSettingCheckboxChecked(self::SETTINGS_BLOCK_COUNTRY_OVERRIDE_GLOBAL_SETTINGS_KEY, self::SETTINGS_BLOCK_COUNTRY_OVERRIDE_GLOBAL_SETTINGS_KEY, $formId)) {
			$countryDatasetValue = UtilsSettingsHelper::getSettingValueWithFallback(self::SETTINGS_BLOCK_COUNTRY_DATA_SET_KEY, self::SETTINGS_BLOCK_COUNTRY_DATA_SET_GLOBAL_KEY, 'default', $formId);
		} else {
			$countryDatasetValue = UtilsSettingsHelper::getOptionValueWithFallback(self::SETTINGS_BLOCK_COUNTRY_DATA_SET_GLOBAL_KEY, 'default');
		}

		if (UtilsSettingsHelper::isSettingCheckboxChecked(self::SETTINGS_BLOCK_PHONE_OVERRIDE_GLOBAL_SETTINGS_KEY, self::SETTINGS_BLOCK_PHONE_OVERRIDE_GLOBAL_SETTINGS_KEY, $formId)) {
			$phoneDatasetValue = UtilsSettingsHelper::getSettingValueWithFallback(self::SETTINGS_BLOCK_PHONE_DATA_SET_KEY, self::SETTINGS_BLOCK_PHONE_DATA_SET_GLOBAL_KEY, 'default', $formId);
		} else {
			$phoneDatasetValue = UtilsSettingsHelper::getOptionValueWithFallback(self::SETTINGS_BLOCK_PHONE_DATA_SET_GLOBAL_KEY, 'default');
		}

		$geolocation = \strtolower($this->geolocation->getUsersGeolocation());

		$preselectedValue = self::SETTINGS_BLOCK_COUNTRY_FALLBACK_VALUE_KEY;
		if ($geolocation !== 'localhost') {
			$preselectedValue = $geolocation;
		}

		return [
			'country' => [
				'dataset' => $countryDatasetValue,
				'preselectedValue' => $preselectedValue,
			],
			'phone' => [
				'dataset' => $phoneDatasetValue,
				'preselectedValue' => $preselectedValue,
			],
			'countries' => $this->countries->getCountriesDataSet(),
		];
	}

	/**
	 * Get one settings country list output
	 *
	 * @param string $selectedValue Selected value.
	 * @param string $list List of countries.
	 *
	 * @return array<string, mixed>
	 */
	private function getCountrySettingsList(string $selectedValue, string $list): array
	{
		return \array_map(
			function ($option) use ($selectedValue) {
				$label = $option['label'] ?? '';
				$value = $option['value'] ?? '';

				if (!$label || !$value) {
					return;
				}

				return [
					'component' => 'select-option',
					'selectOptionLabel' => $label,
					'selectOptionValue' => $value,
					'selectOptionIsSelected' => $value === $selectedValue,
				];
			},
			$this->countries->getCountriesDataSet(false)[$list]
		);
	}
}
