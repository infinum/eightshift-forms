<?php

/**
 * Custom data block settings class.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsBlocks class.
 */
class SettingsBlocks implements SettingGlobalInterface, ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_blocks';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'blocks';

	/**
	 * Filter country block data set key.
	 */
	public const FILTER_BLOCK_COUNTRY_DATA_SET_NAME = 'es_forms_block_country_data_set';

	/**
	 * Transient cache name for block country data set. No need to flush it because it is short live.
	 */
	public const CACHE_BLOCK_COUNTRY_DATE_SET_NAME = 'es_block_country_data_set_cache';

	/**
	 * Country default state key.
	 */
	public const SETTINGS_COUNTRY_DEFAULT_KEY = 'block-country-default-state';

	/**
	 * Country default state value key.
	 */
	public const SETTINGS_COUNTRY_DEFAULT_VALUE_KEY = 'us';

	/**
	 * Phone sync with country block key.
	 */
	public const SETTINGS_BLOCK_PHONE_SYNC_KEY = 'block-phone-sync';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_BLOCK_COUNTRY_DATA_SET_NAME, [$this, 'getCountriesDataSet']);
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		$countries = Helper::getDataManifestRaw('country');

		return [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Country', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introSubtitle' => \__('These settings are used in phone and country blocks.', 'eightshift-forms'),
							],
							[
								'component' => 'select',
								'selectFieldLabel' => \__('Preselected phone/country field value.', 'eightshift-forms'),
								'selectFieldHelp' => \__('This value can be changed in settings of every form.', 'eightshift-forms'),
								'selectName' => $this->getSettingsName(self::SETTINGS_COUNTRY_DEFAULT_KEY),
								'selectContent' => \array_map(
									function ($option) {
										$label = $option[0] ?? '';
										$value = $option[1] ?? '';

										if (!$label || !$value) {
											return;
										}

										$countryDefaultValue = $this->getOptionValue(self::SETTINGS_COUNTRY_DEFAULT_KEY);

										return [
											'component' => 'select-option',
											'selectOptionLabel' => $label,
											'selectOptionValue' => $value,
											'selectOptionIsSelected' => $countryDefaultValue ? $value === $countryDefaultValue : $value === self::SETTINGS_COUNTRY_DEFAULT_VALUE_KEY,
										];
									},
									Helper::getCountrySelectList()
								),
							],
							[
								'component' => 'textarea',
								'textareaFieldLabel' => \__('Countries list', 'eightshift-forms'),
								'selectFieldHelp' => \__('This is the lis of our default countries name, iso code and call number prefix.', 'eightshift-forms'),
								'textareaIsReadOnly' => true,
								'textareaValue' => wp_json_encode($this->getCountriesDataSet(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
								'additionalClass' => 'es-textarea--limit-height',
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
								'checkboxesName' => $this->getSettingsName(self::SETTINGS_BLOCK_PHONE_SYNC_KEY),
								'checkboxesContent' => [
									[
										'component' => 'checkbox',
										'checkboxLabel' => \__('Use phone/country sync', 'eightshift-forms'),
										'checkboxIsChecked' => $this->isCheckboxOptionChecked(self::SETTINGS_BLOCK_PHONE_SYNC_KEY, self::SETTINGS_BLOCK_PHONE_SYNC_KEY),
										'checkboxValue' => self::SETTINGS_BLOCK_PHONE_SYNC_KEY,
										'checkboxSingleSubmit' => true,
										'checkboxAsToggle' => true,
										'checkboxAsToggleSize' => 'medium',
									]
								]
							],
						],
					],
				],
			],
		];
	}


	/**
	 * Get countries data set depending on the provided filter and default set.
	 *
	 * @return array
	 */
	public function getCountriesDataSet($useFullOutput = true): array
	{
		$output = \get_transient(SettingsBlocks::CACHE_BLOCK_COUNTRY_DATE_SET_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		if (!$output) {
			$countries = Helper::getCountrySelectList();
			$output = [
				'default' => [
					'label' => __('Default', 'eightshift-forms'),
					'slug' => 'default',
					'items' => $countries,
					'codes' => array_map(
						static function($item) {
							return [
								'label' => $item[0],
								'value' => $item[1],
							];
						},
						$countries
					)
				]
			];

			$alternative = [];
			$filterName = Filters::getBlockFilterName('country', 'alternativeDataSet');
			if (\has_filter($filterName)) {
				$alternative = \apply_filters($filterName, []);
			}

			$alternativeOutput = [];

			if ($alternative) {
				foreach ($alternative as $value) {
					$label = $value['label'] ?? '';
					$slug = $value['slug'] ?? '';
					$removed = $value['remove'] ? array_flip($value['remove']) : [];
					$changed = $value['change'] ?? [];

					if (!$label || !$slug) {
						continue;
					}

					$slug = strtolower(str_replace(' ', '-', $slug));

					$alternativeOutput[$slug] = [
						'label' => $label,
						'slug' => $slug,
						'items' => $countries,
					];

					foreach ($countries as $key => $item) {
						$countryCode = $item[1] ? strtolower($item[1]) : '';

						// Remove item from list.
						if (isset($removed[$countryCode])) {
							unset($alternativeOutput[$slug]['items'][$key]);
						}

						// Change label in the list.
						foreach ($changed as $changedKey => $changedValue) {
							if ($countryCode === $changedKey) {
								$alternativeOutput[$slug]['items'][$key][0] = $changedValue;
							}
						}
					}
				}
			}

			$output = array_merge(
				$output,
				$alternativeOutput,
			);

			\set_transient(SettingsBlocks::CACHE_BLOCK_COUNTRY_DATE_SET_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['quick']);
		}

		if ($useFullOutput) {
			return $output;
		}

		return [
			'label' => $output['default']['label'],
			'slug' => $output['default']['slug'],
			'items' => array_values(array_map(
				static function($item) {
					return [
						'label' => $item['label'],
						'value' => $item['slug'],
					];
				},
				$output
			)),
			'codes' => $output['default']['codes'],
		];
	}
}
