<?php

/**
 * File that holds class for admin content listing.
 *
 * @package EightshiftForms\Settings\Listing
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Listing;

use EightshiftForms\Config\Config;
use EightshiftForms\CustomPostType\Forms;

/**
 * FormsListing class.
 */
class FormsListing implements FormListingInterface
{

	/**
	 * Get Form List items.
	 *
	 * @return array
	 */
	public function getFormsList(): array
	{
		$args = [
			'post_type' => Forms::POST_TYPE_SLUG,
			'posts_per_page' => 5000, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
		];

		$theQuery = new \WP_Query($args);

		if ($theQuery->have_posts()) {
			while ($theQuery->have_posts()) {
				$theQuery->the_post();

				$id = get_the_ID();

				$output[] = [
					'id' => $id,
					'title' => get_the_title($id),
					'slug' => \get_the_permalink($id),
					'status' => \get_post_status($id),
					'settingsLink' => Config::getOptionsPageUrl((string) $id),
					'editLink' => Config::getFormEditPageUrl((string) $id),
				];
			}
		}

		return $output;
	}
}
