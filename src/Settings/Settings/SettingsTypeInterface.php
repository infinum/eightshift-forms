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
interface SettingsTypeInterface
{
	/**
	 * Get Form options array
	 *
	 * @return array
	 */
	public function getSettingsTypeData(): array;
}
