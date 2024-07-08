<?php

/**
 * ResultOutput settings class.
 *
 * @package EightshiftForms\ResultOutput
 */

declare(strict_types=1);

namespace EightshiftForms\ResultOutput;

use EightshiftForms\CustomPostType\Result;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsResultOutput class.
 */
class SettingsResultOutput implements UtilsSettingGlobalInterface, ServiceInterface
{
	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_result_output';

	/**
	 * Filter settings is Valid key.
	 */
	public const FILTER_SETTINGS_IS_VALID_NAME = 'es_forms_settings_is_valid_result_output';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'result-output';

	/**
	 * Result output use key.
	 */
	public const SETTINGS_RESULT_OUTPUT_USE_KEY = 'result-output-use';

	/**
	 * Url prefix key.
	 */
	public const SETTINGS_RESULT_OUTPUT_URL_PREFIX_KEY = 'result-output-url-prefix';


	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		$isUsed = UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_RESULT_OUTPUT_USE_KEY, self::SETTINGS_RESULT_OUTPUT_USE_KEY);

		if (!$isUsed) {
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
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_RESULT_OUTPUT_USE_KEY, self::SETTINGS_RESULT_OUTPUT_USE_KEY)) {
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Internal storage', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introSubtitle' => \__('If you change these options make sure you resave your permalinks under settings > permalinks.', 'eightshift-forms'),
								'introIsHighlighted' => true,
								'introIsHighlightedImportant' => true,
							],
							[
								'component' => 'input',
								'inputName' => UtilsSettingsHelper::getOptionName(self::SETTINGS_RESULT_OUTPUT_URL_PREFIX_KEY),
								'inputFieldLabel' => \__('Global url prefix', 'eightshift-forms'),
								'inputFieldHelp' => \__('Define a global prefix for all the result output urls. If you set this value with "/" your result outputs will not have a prefix but be careful as the created outputs can colide with other pages.', 'eightshift-forms'),
								'inputType' => 'text',
								'inputPlaceholder' => Result::POST_TYPE_URL_SLUG,
								'inputValue' => UtilsSettingsHelper::getOptionValue(self::SETTINGS_RESULT_OUTPUT_URL_PREFIX_KEY),
							],
						],
					],
				],
			],
		];
	}
}
