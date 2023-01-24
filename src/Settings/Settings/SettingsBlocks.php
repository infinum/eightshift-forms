<?php

/**
 * Custom data block settings class.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Helpers\Helper;
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
	 * Country default state key.
	 */
	public const SETTINGS_COUNTRY_DEFAULT_KEY = 'country-default-state';

	/**
	 * Country default state value key.
	 */
	public const SETTINGS_COUNTRY_DEFAULT_VALUE_KEY = 'us';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
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
						'tabLabel' => \__('Countries', 'eightshift-forms'),
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
								'textareaValue' => $countries,
							],
						],
					],
				],
			],
		];
	}
}
