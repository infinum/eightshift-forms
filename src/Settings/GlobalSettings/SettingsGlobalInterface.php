<?php

/**
 * Class that holds data for admin forms global settings.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\GlobalSettings;

/**
 * Interface for SettingsGlobalInterface
 */
interface SettingsGlobalInterface
{
	/**
	 * Get all settings sidebar array for building settings page.
	 *
	 * @param string $type Form Type to show.
	 *
	 * @return array
	 */
	public function getSettingsSidebar(string $type): array;

	/**
	 * Get all settings array for building settings page.
	 *
	 * @param string $type Form Type to show.
	 *
	 * @return string
	 */
	public function getSettingsForm(string $type): string;
}
