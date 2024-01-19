<?php

/**
 * General Settings class.
 *
 * @package EightshiftForms\Dashboard
 */

declare(strict_types=1);

namespace EightshiftForms\Dashboard;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsDashboard class.
 */
class SettingsDashboard implements UtilsSettingGlobalInterface, ServiceInterface
{
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_dashboard';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_dashboard';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = UtilsConfig::SLUG_ADMIN_DASHBOARD;

	/**
	 * Register all the hooks.
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
		$filtered = [];

		$data = \apply_filters(UtilsConfig::FILTER_SETTINGS_DATA, []);

		foreach ($data as $key => $value) {
			$use = $value['use'] ?? '';

			if (!$use) {
				continue;
			}

			$checked = UtilsSettingsHelper::isOptionCheckboxChecked($use, $use);

			$labels = $value['labels'] ?? [];

			$item = [
				'component' => 'card-inline',
				'cardInlineTitle' => $labels['title'] ?? '',
				'cardInlineIcon' => $labels['icon'] ?? '',
				'cardInlineRightContent' => [
					$checked ? [
						'component' => 'submit',
						'submitVariant' => 'ghost',
						'submitButtonAsLink' => true,
						'submitButtonAsLinkUrl' => UtilsGeneralHelper::getSettingsGlobalPageUrl($key),
						'submitValue' => \__('Edit', 'eightshift-forms'),
					] : [],
					[
						'component' => 'checkboxes',
						'checkboxesName' => UtilsSettingsHelper::getOptionName($use),
						'checkboxesContent' => [
							[
								'component' => 'checkbox',
								'checkboxHideLabelText' => true,
								'checkboxIsChecked' => $checked,
								'checkboxValue' => $use,
								'checkboxSingleSubmit' => true,
								'checkboxAsToggle' => true,
								'checkboxAsToggleSize' => 'medium',
							],
						],
					],
				]
			];

			$filtered[$value['type']][] = $item;
		}

		$output = [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
		];

		// Order output in the correct order.
		foreach (UtilsSettingsHelper::sortSettingsByOrder($filtered) as $key => $value) {
			$output[] = [
				'component' => 'layout',
				'layoutContent' => [
					[
						'component' => 'intro',
						'introTitle' => $data[$key]['labels']['title'] ?? '',
						'introIsHeading' => false,
						'introTitleSize' => 'small',
					],
					...$value,
				],
				'layoutType' => 'layout-v-stack-clean',
			];
		}

		return $output;
	}
}
