<?php

/**
 * General Settings class.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsDashboard class.
 */
class SettingsDashboard implements SettingsDataInterface, ServiceInterface
{
	/**
	 * Use dashboard helper trait.
	 */
	use SettingsHelper;

	/**
	 * Filter settings sidebar key.
	 */
	public const FILTER_SETTINGS_SIDEBAR_NAME = 'es_forms_settings_sidebar_dashboard';

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
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_SIDEBAR_NAME, [$this, 'getSettingsSidebar']);
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
	}

	/**
	 * Get Settings sidebar data.
	 *
	 * @return array<string, mixed>
	 */
	public function getSettingsSidebar(): array
	{
		return [
			'label' => \__('Dashboard', 'eightshift-forms'),
			'value' => self::SETTINGS_TYPE_KEY,
			'icon' => Filters::ALL[self::SETTINGS_TYPE_KEY]['icon'],
			'type' => SettingsAll::SETTINGS_SIEDBAR_TYPE_GENERAL,
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
		$output = [];

		foreach (Filters::ALL as $key => $value) {
			$dashboard = $value['dashboard'] ?? [];

			if (!$dashboard) {
				continue;
			}

			$icon = $value['icon'] ?? '';
			$useKey = $value['dashboard']['key'] ?? '';
			$external = $value['dashboard']['external'] ?? '';
			$type = $value['type'] ?? SettingsAll::SETTINGS_SIEDBAR_TYPE_GENERAL;
			$label = \ucwords(\str_replace('-', ' ', $key));

			$output[$type][] = [
				'component' => 'card',
				'cardTitle' => $label,
				'cardIcon' => $icon,
				'cardLinks' => [
					[
						'label' => \__('Settings', 'eightshift-forms'),
						'url' => Helper::getSettingsGlobalPageUrl($key),
					],
					[
						'label' => \__('External', 'eightshift-forms'),
						'url' => $external,
					]
				],
				'cardToggle' => [
					[
						'component' => 'checkboxes',
						'checkboxesFieldSkip' => true,
						'checkboxesName' => $this->getSettingsName($useKey),
						'checkboxesId' => $this->getSettingsName($useKey),
						'checkboxesIsRequired' => true,
						'checkboxesContent' => [
							[
								'component' => 'checkbox',
								'checkboxLabel' => $key,
								'checkboxIsChecked' => $this->isCheckboxOptionChecked($useKey, $useKey),
								'checkboxValue' => $useKey,
								'checkboxAsToggle' => true,
								'checkboxAsToggleSize' => 'medium',
								'checkboxHideLabelText' => true,
							],
						],
					],
				]
			];
		}

		$a = [
			[
				'component' => 'intro',
				'introIsFirst' => true,
				'introTitle' => \__('Dashboard', 'eightshift-forms'),
				'introSubtitle' => \__('In these settings, you decide all the features you would like to use in your project. Once the feature is turned on, you will see additional settings in the sidebar.', 'eightshift-forms'),
			],
			[
				'component' => 'intro',
				'introTitle' => \__('General', 'eightshift-forms'),
				'introIsHeading' => true,
			],
			[
				'component' => 'layout',
				'layoutItems' => $output[SettingsAll::SETTINGS_SIEDBAR_TYPE_GENERAL],
			],
			[
				'component' => 'intro',
				'introIsHeading' => true,
				'introTitle' => \__('Integrations', 'eightshift-forms'),
			],
			[
				'component' => 'layout',
				'layoutItems' => $output[SettingsAll::SETTINGS_SIEDBAR_TYPE_INTEGRATION],
			],
			[
				'component' => 'intro',
				'introIsHeading' => true,
				'introTitle' => \__('Troubleshooting', 'eightshift-forms'),
			],
			[
				'component' => 'layout',
				'layoutItems' => $output[SettingsAll::SETTINGS_SIEDBAR_TYPE_TROUBLESHOOTING],
			],
		];

		return $a;
	}
}
