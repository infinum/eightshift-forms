<?php

/**
 * Interface that holds all methods for building admin settings pages forms.
 *
 * @package EightshiftForms\Settings\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Settings;

/**
 * Interface for admin content listing
 */
interface SettingsAllInterface
{
	/**
	 * Set all settings page field keys.
	 *
	 * @param string $formId Form ID.
	 * @param string $Option Form Type to show.
	 *
	 * @return array
	 */
	public function getSettingsAll(string $formId, string $option): array;
}
