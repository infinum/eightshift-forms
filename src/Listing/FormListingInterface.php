<?php

/**
 * Class that holds data for admin forms listing.
 *
 * @package EightshiftForms\Listing
 */

declare(strict_types=1);

namespace EightshiftForms\Listing;

/**
 * Interface for admin content listing
 */
interface FormListingInterface
{
	/**
	 * Get Forms List.
	 *
	 * @param string $type Type of listing to output.
	 * @param string $parent Parent type for listing to output.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getFormsList(string $type = '', string $parent = ''): array;
}
