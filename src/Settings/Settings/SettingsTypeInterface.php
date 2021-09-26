<?php

/**
 * Interface that holds all methods for building single form settings form.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

/**
 * Interface for SettingsTypeInterface
 */
interface SettingsTypeInterface
{
	/**
	 * Get Form settings data array
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array
	 */
	public function getSettingsTypeData(string $formId): array;
}
