<?php

/**
 * Custom data block settings class.
 *
 * @package EightshiftForms\Blocks
 */

declare(strict_types=1);

namespace EightshiftForms\Blocks;

use EightshiftForms\Countries\CountriesInterface;
use EightshiftForms\Settings\SettingGlobalInterface;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Helpers\SettingsOutputHelpers;
use EightshiftForms\Settings\SettingInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsBlocks class.
 */
class SettingsBlocks implements SettingGlobalInterface, SettingInterface, ServiceInterface
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
	public const SETTINGS_BLOCK_COUNTRY_DATA_SET_KEY = 'block-country-data-set';
	public const SETTINGS_BLOCK_COUNTRY_DATA_SET_GLOBAL_KEY = self::SETTINGS_BLOCK_COUNTRY_DATA_SET_KEY . '-global';

	/**
	 * Phone keys.
	 */
	public const SETTINGS_BLOCK_PHONE_OVERRIDE_GLOBAL_SETTINGS_KEY = 'block-phone-override-global-settings';
	public const SETTINGS_BLOCK_PHONE_DATA_SET_KEY = 'block-phone-data-set';
	public const SETTINGS_BLOCK_PHONE_DATA_SET_GLOBAL_KEY = self::SETTINGS_BLOCK_PHONE_DATA_SET_KEY . '-global';
	public const SETTINGS_BLOCK_PHONE_DISABLE_PICKER_KEY = 'block-phone-disable-picker';
	public const SETTINGS_BLOCK_PHONE_USE_COUNTRY_DATA_KEY = 'block-phone-use-country-data';
	public const SETTINGS_BLOCK_PHONE_USE_COUNTRY_DATA_GLOBAL_KEY = self::SETTINGS_BLOCK_PHONE_USE_COUNTRY_DATA_KEY . '-global';

	/**
	 * Instance variable of countries data.
	 *
	 * @var CountriesInterface
	 */
	private CountriesInterface $countries;

	/**
	 * Create a new admin instance.
	 *
	 * @param CountriesInterface $countries Inject countries which holds data about for storing to countries.
	 */
	public function __construct(CountriesInterface $countries)
	{
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
		$overrideGlobalSettingsCountry = SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_BLOCK_COUNTRY_OVERRIDE_GLOBAL_SETTINGS_KEY, self::SETTINGS_BLOCK_COUNTRY_OVERRIDE_GLOBAL_SETTINGS_KEY, $formId);
		$overrideGlobalSettingsPhone = SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_BLOCK_PHONE_OVERRIDE_GLOBAL_SETTINGS_KEY, self::SETTINGS_BLOCK_PHONE_OVERRIDE_GLOBAL_SETTINGS_KEY, $formId);

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
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
								'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_BLOCK_COUNTRY_OVERRIDE_GLOBAL_SETTINGS_KEY),
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
									'selectName' => SettingsHelpers::getSettingName(self::SETTINGS_BLOCK_COUNTRY_DATA_SET_KEY),
									'selectContent' => $this->getCountrySettingsList(
										SettingsHelpers::getSettingValueWithFallback(self::SETTINGS_BLOCK_COUNTRY_DATA_SET_KEY, self::SETTINGS_BLOCK_COUNTRY_DATA_SET_GLOBAL_KEY, 'default', $formId),
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
								'checkboxesName' => SettingsHelpers::getSettingName(self::SETTINGS_BLOCK_PHONE_OVERRIDE_GLOBAL_SETTINGS_KEY),
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
									'selectName' => SettingsHelpers::getSettingName(self::SETTINGS_BLOCK_PHONE_DATA_SET_KEY),
									'selectContent' => $this->getCountrySettingsList(
										SettingsHelpers::getSettingValueWithFallback(self::SETTINGS_BLOCK_PHONE_DATA_SET_KEY, self::SETTINGS_BLOCK_PHONE_DATA_SET_GLOBAL_KEY, 'default', $formId),
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
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		$disablePhoneCountryPicker = SettingsHelpers::isOptionCheckboxChecked(self::SETTINGS_BLOCK_PHONE_DISABLE_PICKER_KEY, self::SETTINGS_BLOCK_PHONE_DISABLE_PICKER_KEY);

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
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
								'selectName' => SettingsHelpers::getOptionName(self::SETTINGS_BLOCK_COUNTRY_DATA_SET_GLOBAL_KEY),
								'selectContent' => $this->getCountrySettingsList(
									SettingsHelpers::getOptionValueWithFallback(self::SETTINGS_BLOCK_COUNTRY_DATA_SET_GLOBAL_KEY, 'default'),
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
								'checkboxesName' => SettingsHelpers::getOptionName(self::SETTINGS_BLOCK_PHONE_DISABLE_PICKER_KEY),
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
									'selectName' => SettingsHelpers::getOptionName(self::SETTINGS_BLOCK_PHONE_DATA_SET_GLOBAL_KEY),
									'selectContent' => $this->getCountrySettingsList(
										SettingsHelpers::getOptionValueWithFallback(self::SETTINGS_BLOCK_PHONE_DATA_SET_GLOBAL_KEY, 'default'),
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
		if (SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_BLOCK_COUNTRY_OVERRIDE_GLOBAL_SETTINGS_KEY, self::SETTINGS_BLOCK_COUNTRY_OVERRIDE_GLOBAL_SETTINGS_KEY, $formId)) {
			$countryDatasetValue = SettingsHelpers::getSettingValueWithFallback(self::SETTINGS_BLOCK_COUNTRY_DATA_SET_KEY, self::SETTINGS_BLOCK_COUNTRY_DATA_SET_GLOBAL_KEY, 'default', $formId);
		} else {
			$countryDatasetValue = SettingsHelpers::getOptionValueWithFallback(self::SETTINGS_BLOCK_COUNTRY_DATA_SET_GLOBAL_KEY, 'default');
		}

		if (SettingsHelpers::isSettingCheckboxChecked(self::SETTINGS_BLOCK_PHONE_OVERRIDE_GLOBAL_SETTINGS_KEY, self::SETTINGS_BLOCK_PHONE_OVERRIDE_GLOBAL_SETTINGS_KEY, $formId)) {
			$phoneDatasetValue = SettingsHelpers::getSettingValueWithFallback(self::SETTINGS_BLOCK_PHONE_DATA_SET_KEY, self::SETTINGS_BLOCK_PHONE_DATA_SET_GLOBAL_KEY, 'default', $formId);
		} else {
			$phoneDatasetValue = SettingsHelpers::getOptionValueWithFallback(self::SETTINGS_BLOCK_PHONE_DATA_SET_GLOBAL_KEY, 'default');
		}

		return [
			'country' => [
				'dataset' => $countryDatasetValue,
			],
			'phone' => [
				'dataset' => $phoneDatasetValue,
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
