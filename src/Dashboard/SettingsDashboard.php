<?php

/**
 * General Settings class.
 *
 * @package EightshiftForms\Dashboard
 */

declare(strict_types=1);

namespace EightshiftForms\Dashboard;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\Settings\SettingGlobalInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsDashboard class.
 */
class SettingsDashboard implements SettingGlobalInterface, ServiceInterface
{
	/**
	 * Use dashboard helper trait.
	 */
	use SettingsHelper;

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
	public const SETTINGS_TYPE_KEY = 'dashboard';

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

		$items = Filters::getSettingsFiltersData();

		// Load addinal sidebar from addons.
		$filterName = Filters::getFilterName(['admin', 'settings', 'config']);
		if (\has_filter($filterName)) {
			$items = \array_merge(
				$items,
				\apply_filters($filterName, [])
			);
		}

		foreach ($items as $key => $value) {
			$use = $value['use'] ?? '';

			if (!$use) {
				continue;
			}

			$checked = $this->isOptionCheckboxChecked($use, $use);

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
						'submitButtonAsLinkUrl' => Helper::getSettingsGlobalPageUrl($key),
						'submitValue' => \__('Edit', 'eightshift-forms'),
					] : [],
					[
						'component' => 'checkboxes',
						'checkboxesName' => $this->getOptionName($use),
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
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
		];

		// Order output in the correct order.
		foreach ($this->sortSettingsByOrder($filtered) as $key => $value) {
			$output[] = [
				'component' => 'layout',
				'layoutContent' => [
					[
						'component' => 'intro',
						'introTitle' => Filters::getSettingsLabels($key),
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
