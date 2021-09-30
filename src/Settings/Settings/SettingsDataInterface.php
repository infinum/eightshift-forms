<?php

/**
 * Interface that holds all methods for building single form settings form.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

/**
 * Interface for SettingsDataInterface
 */
interface SettingsDataInterface
{
	/**
	 * Get Settings sidebar data.
	 *
	 * @return array
	 */
	public function getSettingsSidebar(): array;

	/**
	 * Get Form settings data array
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array
	 */
	public function getSettingsData(string $formId): array;
}
