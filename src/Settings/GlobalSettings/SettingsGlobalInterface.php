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
	 * Get all settings array for building settings page.
	 *
	 * @param string $type Form Type to show.
	 *
	 * @return array
	 */
	public function getSettingsGlobal(string $type): array;
}
