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
	 * @param string $status Status for listing to output.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getFormsList(string $status): array;
}
