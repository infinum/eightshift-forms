<?php

/**
 * General Settings class.
 *
 * @package EightshiftForms\Dashboard
 */

declare(strict_types=1);

namespace EightshiftForms\Dashboard;

use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\SettingsOutputHelpers;
use EightshiftForms\Config\Config;
use EightshiftForms\Settings\SettingGlobalInterface;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsDashboard class.
 */
class SettingsDashboard implements SettingGlobalInterface, ServiceInterface
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
	public const SETTINGS_TYPE_KEY = Config::SLUG_ADMIN_DASHBOARD;

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

		$data = \apply_filters(Config::FILTER_SETTINGS_DATA, []);

		foreach ($data as $key => $value) {
			$settingsGlobal = $value['settingsGlobal'] ?? '';
			$use = $value['use'] ?? '';
			$type = $value['type'] ?? '';
			$labels = $value['labels'] ?? [];
			$icon = $labels['icon'] ?? '';
			$title = $labels['title'] ?? '';

			if ($key === SettingsDashboard::SETTINGS_TYPE_KEY) {
				continue;
			}

			if (!$settingsGlobal) {
				continue;
			}

			$checked = SettingsHelpers::isOptionCheckboxChecked($use, $use);

			$item = [
				'component' => 'card-inline',
				'cardInlineTitle' => $title,
				'cardInlineIcon' => $icon,
				'cardInlineRightContent' => [
					[
						'component' => 'checkboxes',
						'checkboxesName' => SettingsHelpers::getOptionName($use),
						'checkboxesContent' => [
							[
								'component' => 'checkbox',
								'checkboxHideLabelText' => true,
								'checkboxIsChecked' => $checked,
								'checkboxValue' => $use,
								'checkboxSingleSubmit' => true,
								'checkboxAsToggle' => true,
							],
						],
					],
					[
						'component' => 'button',
						'buttonVariant' => 'primaryGhost',
						'buttonUrl' => GeneralHelpers::getSettingsGlobalPageUrl($key),
						'buttonIsDisabled' => !$checked,
						'buttonLabel' => \__('Edit', 'eightshift-forms'),
					],
				],
			];

			if ($type) {
				$filtered[$type][] = $item;
			}
		}

		$output = [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
		];

		// Order output in the correct order.
		foreach (SettingsHelpers::sortSettingsByOrder($filtered) as $key => $value) {
			$output[] = [
				'component' => 'layout',
				'layoutContent' => [
					[
						'component' => 'intro',
						'introTitle' => $data[$key]['labels']['title'] ?? '',
					],
					...$value,
				],
				'additionalClass' => 'esf:gap-5!',
			];
		}

		return $output;
	}
}
