<?php

/**
 * Class that holds data for admin forms listing.
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
	 * Get Forms List.
	 *
	 * @return array<int, array<string, int|string|bool>>
	 */
	public function getFormsList(): array;
}
