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
	 * @param array<string, mixed> $additionalQuery Additional query arguments.
	 * @param bool $showTrash Whether to show trash posts.
	 *
	 * @return array<mixed>
	 */
	public function getFormsList(array $additionalQuery = [], bool $showTrash = false): array;
}
