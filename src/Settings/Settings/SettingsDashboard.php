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
		return [
			[
				'component' => 'intro',
				"introIsFirst" => true,
				'introTitle' => \__('Dashboard', 'eightshift-forms'),
				'introSubtitle' => \__('In these settings, you decide all the features you would like to use in your project. Once the feature is turned on, you will see additional settings in the sidebar.', 'eightshift-forms'),
			],
			[
				'component' => 'layout',
				'layoutItems' => \array_values(\array_filter(\array_map(
					function ($item, $keys) {
						$dashboard = $item['dashboard'] ?? [];
		
						if (!$dashboard) {
							return false;
						}
	
						$icon = $item['icon'] ?? '';
						$key = $dashboard['key'] ?? '';
						$external = $dashboard['external'] ?? '';
						$type = $dashboard['type'] ?? '';
						$label = \ucwords(\str_replace('-', ' ', $keys));
	
						return [
							'component' => 'card',
							'cardTitle' => $label,
							'cardIcon' => $icon,
							'cardLinks' => [
								[
									'label' => \__('Settings', 'eightshift-forms'),
									'url' => Helper::getSettingsGlobalPageUrl($keys),
								],
								[
									'label' => \__('External', 'eightshift-forms'),
									'url' => $external,
								]
							],
							'cardToggle' => [
								[
									'component' => 'checkboxes',
									'checkboxesFieldLabel' => '',
									'checkboxesName' => $this->getSettingsName($key),
									'checkboxesId' => $this->getSettingsName($key),
									'checkboxesIsRequired' => true,
									'checkboxesContent' => [
										[
											'component' => 'checkbox',
											'checkboxLabel' => $key,
											'checkboxIsChecked' => $this->isCheckboxOptionChecked($key, $key),
											'checkboxValue' => $key,
											'checkboxAsToggle' => true,
											'checkboxAsToggleSize' => 'medium',
											'checkboxHideLabelText' => true,
										],
									],
								],
							]
						];
					},
					Filters::ALL,
					array_keys(Filters::ALL)
				))),
			],
		];
	}
}
