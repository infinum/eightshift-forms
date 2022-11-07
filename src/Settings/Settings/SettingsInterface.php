<?php

/**
 * Class that holds data for admin forms settings.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

/**
 * Interface for SettingsInterface
 */
interface SettingsInterface
{
	/**
	 * Get all settings sidebar array for building settings page.
	 *
	 * @return array<string, mixed>
	 */
	public function getSettingsSidebar(string $formId = ''): array;

	/**
	 * Get all settings array for building settings page.
	 *
	 * @param string $type Form Type to show.
	 * @param string $formId Form ID.
	 *
	 * @return string
	 */
	public function getSettingsForm(string $type, string $formId = ''): string;
}
