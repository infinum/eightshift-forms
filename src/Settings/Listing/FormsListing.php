<?php

/**
 * Class that holds data for admin forms listings.
 *
 * @package EightshiftForms\Settings\Listing
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Listing;

use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\Helpers\Helper;

/**
 * FormsListing class.
 */
class FormsListing implements FormListingInterface
{

	/**
	 * Get Forms List.
	 *
	 * @return array
	 */
	public function getFormsList(): array
	{
		// Prepare query args.
		$args = [
			'post_type' => Forms::POST_TYPE_SLUG,
			'posts_per_page' => 5000, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
		];

		$theQuery = new \WP_Query($args);

		$output = [];

		if ($theQuery->have_posts()) {
			while ($theQuery->have_posts()) {
				$theQuery->the_post();

				$id = get_the_ID();

				// Output predefined array of data.
				$output[] = [
					'id' => $id,
					'title' => get_the_title($id),
					'slug' => \get_the_permalink($id),
					'status' => \get_post_status($id),
					'settingsLink' => Helper::getOptionsPageUrl((string) $id),
					'editLink' => Helper::getFormEditPageUrl((string) $id),
				];
			}
		}

		return $output;
	}
}
