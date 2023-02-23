<?php

/**
 * Interface that holds all methods for building single form settings form.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

/**
 * Interface for SettingInterface
 */
interface SettingInterface
{
	/**
	 * Get Form settings data array
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsData(string $formId): array;
}
