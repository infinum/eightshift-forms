<?php

/**
 * Interface that holds all methods for getting forms location usage.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

/**
 * Interface for SettingsLocationInterface
 */
interface SettingsLocationInterface
{
	/**
	 * Return all posts where form is assigned.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<int, mixed>
	 */
	public function getBlockLocations(string $formId): array;
}
