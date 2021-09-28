<?php

/**
 * Class that holds data for global forms settings.
 *
 * @package EightshiftForms\Settings\GlobalSettings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\GlobalSettings;

use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Integrations\Integrations;
use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftForms\Settings\GlobalSettings\SettingsGlobalInterface;

/**
 * SettingsGlobal class.
 */
class SettingsGlobal extends AbstractFormBuilder implements SettingsGlobalInterface
{

	/**
	 * All global settings.
	 */
	public const GLOBAL_SETTINGS = [
		SettingsGeneral::SETTINGS_TYPE_KEY => SettingsGeneral::FILTER_SETTINGS_GLOBAL_NAME,
	];

	/**
	 * Get all settings sidebar array for building settings page.
	 *
	 * @param string $type Form Type to show.
	 *
	 * @return array
	 */
	public function getSettingsSidebar(string $type): array
	{
		$output = [];

		// Loop all settings.
		foreach ($this->getAllSettings() as $filter) {
			// Determin if there is a filter for settings page.
			if (!has_filter($filter)) {
				continue;
			}

			// Get filter data.
			$data = apply_filters($filter, '');

			// If empty array skip.
			if (!$data) {
				continue;
			}

			// Check sidebar value for type.
			$value = $data['sidebar']['value'] ?? '';

			// Populate sidebar data.
			$output[$value] = $data['sidebar'] ?? [];
		}

		// Return all settings data.
		return $output;
	}


	/**
	 * Get all settings array for building settings page.
	 *
	 * @param string $type Form Type to show.
	 *
	 * @return string
	 */
	public function getSettingsForm(string $type): string
	{
		// Check if type is set if not use general settings page.
		if (empty($type)) {
			$type = SettingsGeneral::SETTINGS_TYPE_KEY;
		}

		// Fiund settings page.
		$filter = $this->getAllSettings()[$type] ?? '';

		// Determin if there is a filter for settings page.
		if (!has_filter($filter)) {
			return '';
		}

		// Get filter data.
		$data = apply_filters($filter, '');

		// Populate and build form.
		return $this->buildSettingsForm(
			$data['form'] ?? []
		);
	}

	/**
	 * Get all integration settings merged with global settings.
	 *
	 * @return array
	 */
	private function getAllSettings(): array
	{
		$allSettings = self::GLOBAL_SETTINGS;

		foreach (Integrations::ALL_INTEGRATIONS as $key => $integration) {
			$integrations[$key] = $integration['global'] ?? '';
		}

		return $allSettings;
	}
}
