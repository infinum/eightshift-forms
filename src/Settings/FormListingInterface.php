<?php

/**
 * File that holds class for admin content listing.
 *
 * @package EightshiftLibs\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings;

/**
 * Interface for admin content listing
 */
interface FormListingInterface
{

		/**
	 * Get Form List items.
	 *
	 * @return array
	 */
	public function getFormsList(): array;
}
