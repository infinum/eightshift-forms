<?php

/**
 * Class that holds data for admin forms settings.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

use EightshiftForms\Settings\Settings\SettingsGeneral;
use EightshiftForms\Form\AbstractFormBuilder;
use EightshiftForms\Hooks\Filters;

/**
 * SettingsAll class.
 */
class SettingsAll extends AbstractFormBuilder implements SettingsAllInterface
{
	/**
	 * Get all settings sidebar array for building settings page.
	 *
	 * @param string $formId Form ID.
	 * @param string $type Form Type to show.
	 *
	 * @return array<int|string, mixed>
	 */
	public function getSettingsSidebar(string $formId, string $type): array
	{
		$output = [];

		if (!$formId) {
			return [];
		}

		// Loop all settings.
		foreach ($this->getAllSettingsSidebars() as $filter) {
			// Determin if there is a filter for settings page.
			if (!has_filter($filter)) {
				continue;
			}

			// Get filter data.
			$data = apply_filters($filter, $formId);

			// If empty array skip.
			if (!$data) {
				continue;
			}

			// Check sidebar value for type.
			$value = $data['value'] ?? '';

			// Populate sidebar data.
			$output[$value] = $data ?? [];
		}

		// Return all settings data.
		return $output;
	}

	/**
	 * Get all settings array for building settings page.
	 *
	 * @param string $formId Form ID.
	 * @param string $type Form Type to show.
	 *
	 * @return string
	 */
	public function getSettingsForm(string $formId, string $type): string
	{
		// Bailout if form id is wrong or empty.
		if (empty($formId)) {
			return '';
		}

		// Check if type is set if not use general settings page.
		if (empty($type)) {
			$type = SettingsGeneral::SETTINGS_TYPE_KEY;
		}

		// Find settings page.
		$filter = $this->getAllSettings()[$type] ?? '';

		// Determin if there is a filter for settings page.
		if (!has_filter($filter)) {
			return '';
		}

		// Get filter data.
		$data = apply_filters($filter, $formId);

		// Add additional props to form component.
		$formAdditionalProps['formPostId'] = $formId;
		$formAdditionalProps['formType'] = $type;

		// Populate and build form.
		return $this->buildSettingsForm(
			$data ?? [],
			$formAdditionalProps
		);
	}

	/**
	 * Get all integration settings sidebar merged with general settings.
	 *
	 * @return array<string, string>
	 */
	private function getAllSettingsSidebars(): array
	{
		$allSettings = [];

		foreach ($this->getAllSettings() as $key => $integration) {
			$filter = Filters::ALL[$key] ?? '';

			if (!$filter) {
				continue;
			}
			$allSettings[$key] = $filter['settingsSidebar'] ?? '';
		}

		return $allSettings;
	}

	/**
	 * Get all integration settings merged with global settings.
	 *
	 * @return array<string, string>
	 */
	private function getAllSettings(): array
	{
		$allSettings = [];

		foreach (Filters::ALL as $key => $integration) {
			$settings = $integration['settings'] ?? '';

			if (!$settings) {
				continue;
			}

			$allSettings[$key] = $settings;
		}

		return $allSettings;
	}
}
