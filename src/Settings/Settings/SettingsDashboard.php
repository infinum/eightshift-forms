<?php

/**
 * General Settings class.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Geolocation\SettingsGeolocation;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsDashboard class.
 */
class SettingsDashboard implements SettingInterface, ServiceInterface
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
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
	}

	/**
	 * Get Form settings data array.
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
		$filtered = [];

		foreach (Filters::ALL as $key => $value) {
			$use = $value['use'] ?? '';

			if (!$use) {
				continue;
			}

			$icon = $value['icon'];
			$type = $value['type'];

			$checked = $this->isCheckboxOptionChecked($use, $use);
			$disabled = false;

			if ($key === SettingsGeolocation::SETTINGS_TYPE_KEY) {
				if (Variables::getGeolocationUse()) {
					$checked = true;
					$disabled = true;
				}
			}

			$filtered[$type][] = [
				'component' => 'card',
				'cardTitle' => Filters::getSettingsLabels($key),
				'cardIcon' => $icon,
				'cardLinks' => [
					[
						'label' => \__('Settings', 'eightshift-forms'),
						'url' => Helper::getSettingsGlobalPageUrl($key),
					],
					[
						'label' => \__('Website', 'eightshift-forms'),
						'url' => Filters::getSettingsLabels($key, 'externalLink'),
					]
				],
				'cardToggle' => [
					[
						'component' => 'checkboxes',
						'checkboxesFieldSkip' => true,
						'checkboxesName' => $this->getSettingsName($use),
						'checkboxesId' => $this->getSettingsName($use),
						'checkboxesIsRequired' => true,
						'checkboxesContent' => [
							[
								'component' => 'checkbox',
								'checkboxLabel' => $key,
								'checkboxIsChecked' => $checked,
								'checkboxIsDisabled' => $disabled,
								'checkboxValue' => $use,
								'checkboxSingleSubmit' => true,
								'checkboxAsToggle' => true,
								'checkboxAsToggleSize' => 'medium',
								'checkboxHideLabelText' => true,
							],
						],
					],
				]
			];
		}

		$output = [
			$this->getIntroOutput(self::SETTINGS_TYPE_KEY),
		];

		foreach ($filtered as $key => $value) {
			$output[] = [
					'component' => 'intro',
					'introTitle' => Filters::getSettingsLabels($key),
					'introIsHeading' => true,
			];
			$output[] = [
				'component' => 'layout',
				'layoutItems' => $value,
			];
		}

		return $output;
	}
}
