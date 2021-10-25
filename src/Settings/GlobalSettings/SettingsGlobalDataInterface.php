<?php

/**
 * Class that holds data for global forms settings.
 *
 * @package EightshiftForms\Settings\GlobalSettings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\GlobalSettings;

/**
 * Interface for SettingsGlobalDataInterface
 */
interface SettingsGlobalDataInterface
{
	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array;
}
