<?php

/**
 * File that holds class for admin content listing.
 *
 * @package EightshiftForms\Settings\Listing
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Listing;

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
