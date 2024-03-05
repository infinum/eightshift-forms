<?php

/**
 * Class that holds data for admin forms listing.
 *
 * @package EightshiftForms\Listing
 */

declare(strict_types=1);

namespace EightshiftForms\Listing;

use EightshiftForms\CustomPostType\Forms;

/**
 * Interface for admin content listing
 */
interface FormListingInterface
{
	/**
	 * Get Forms List.
	 *
	 * @param bool $showTrash Show trashed items.
	 * @param string $postType Post type for listing to output.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getFormsList(bool $showTrash = false, string $postType = Forms::POST_TYPE_SLUG): array;
}
