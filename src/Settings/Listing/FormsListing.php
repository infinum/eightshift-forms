<?php

/**
 * Class that holds data for admin forms listing.
 *
 * @package EightshiftForms\Settings\Listing
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Listing;

use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\General\SettingsGeneral;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\Helper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\IntegrationsHelper;
use WP_Query;

/**
 * FormsListing class.
 */
class FormsListing implements FormListingInterface
{
	/**
	 * Get Forms List.
	 *
	 * @param string $status Status for listing to output.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getFormsList(string $status): array
	{
		// Prepare query args.
		$args = [
			'post_type' => Forms::POST_TYPE_SLUG,
			'posts_per_page' => 5000, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
			'post_status' => $status,
		];

		$theQuery = new WP_Query($args);

		$output = [];

		$permanent = $status === 'trash';

		if (!$theQuery->have_posts()) {
			\wp_reset_postdata();
			return [];
		}

		foreach ($theQuery->get_posts() as $post) {
			$id = (int) $post->ID;

			// Output predefined array of data.
			$output[] = [
				'id' => $id,
				'title' => \get_the_title($id),
				'status' => \get_post_status($id),
				'settingsLink' => !$permanent ? Helper::getSettingsPageUrl((string) $id, SettingsGeneral::SETTINGS_TYPE_KEY) : '',
				'editLink' => !$permanent ? Helper::getFormEditPageUrl((string) $id) : '',
				'trashLink' => Helper::getFormTrashActionUrl((string) $id, $permanent),
				'entriesLink' => Helper::getFormsEntriesPageUrl((string) $id),
				'trashRestoreLink' => Helper::getFormTrashRestoreActionUrl((string) $id),
				'activeIntegration' => IntegrationsHelper::getIntegrationDetailsById((string) $id),
				'useSync' => true,
			];
		}

		\wp_reset_postdata();

		return $output;
	}
}
