<?php

/**
 * Class that holds data for admin forms listing.
 *
 * @package EightshiftForms\Listing
 */

declare(strict_types=1);

namespace EightshiftForms\Listing;

use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\General\SettingsGeneral;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\IntegrationsHelpers;
use WP_Query;

/**
 * FormsListing class.
 */
class FormsListing implements FormListingInterface
{
	/**
	 * Get Forms List.
	 *
	 * @param array<string, mixed> $additionalQuery Additional query arguments.
	 * @param bool $showTrash Whether to show trash posts.
	 *
	 * @return array<mixed>
	 */
	public function getFormsList(array $additionalQuery = [], bool $showTrash = false): array
	{
		// Prepare query args.
		$args = \array_merge([
			'post_type' => Forms::POST_TYPE_SLUG,
			'posts_per_page' => 5000, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
		], $additionalQuery);

		$theQuery = new WP_Query($args);

		$output = [];

		if ($theQuery->have_posts()) {
			foreach ($theQuery->get_posts() as $post) {
				$id = (int) $post->ID;

				// Output predefined array of data.
				$output[] = [
					'id' => $id,
					'title' => \get_the_title($id),
					'status' => \get_post_status($id),
					'settingsLink' => GeneralHelpers::getSettingsPageUrl((string) $id, SettingsGeneral::SETTINGS_TYPE_KEY),
					'editLink' => !$showTrash ? GeneralHelpers::getFormEditPageUrl((string) $id) : '',
					'trashLink' => GeneralHelpers::getFormTrashActionUrl((string) $id, $showTrash),
					'entriesLink' => GeneralHelpers::getListingPageUrl(Config::SLUG_ADMIN_LISTING_ENTRIES, (string) $id),
					'trashRestoreLink' => GeneralHelpers::getFormTrashRestoreActionUrl((string) $id),
					'activeIntegration' => IntegrationsHelpers::getIntegrationDetailsById((string) $id),
					'useSync' => true,
				];
			}
		}

		\wp_reset_postdata();

		return [
			'currentPage' => (int) $theQuery->query_vars['paged'], // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
			'totalPages' => (int) $theQuery->max_num_pages, // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
			'count' => \count($output),
			'items' => $output,
		];
	}
}
