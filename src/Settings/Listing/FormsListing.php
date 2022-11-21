<?php

/**
 * Class that holds data for admin forms listing.
 *
 * @package EightshiftForms\Settings\Listing
 */

declare(strict_types=1);

namespace EightshiftForms\Settings\Listing;

use EightshiftForms\CustomPostType\Forms;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\Settings\SettingsLocation;
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
			'post_status' => $status
		];

		$theQuery = new WP_Query($args);

		$output = [];

		$permanent = $status === 'trash';

		while ($theQuery->have_posts()) {
			$theQuery->the_post();

			$id = (int) \get_the_ID();

			// Output predefined array of data.
			$output[] = [
				'id' => $id,
				'title' => \get_the_title($id),
				'status' => \get_post_status($id),
				'settingsLink' => !$permanent ? Helper::getSettingsPageUrl((string) $id) : '',
				'settingsLocationLink' => !$permanent ? Helper::getSettingsPageUrl((string) $id, SettingsLocation::SETTINGS_TYPE_KEY) : '',
				'editLink' => !$permanent ? Helper::getFormEditPageUrl((string) $id) : '',
				'trashLink' => Helper::getFormTrashActionUrl((string) $id, $permanent),
				'trashRestoreLink' => Helper::getFormTrashRestoreActionUrl((string) $id),
				'activeIntegration' =>  $this->getActiveIntegrationIcons((string) $id),
			];
		}

		return $output;
	}

	/**
	 * Get all active integration on specific form.
	 *
	 * @param string $id Form Id.
	 *
	 * @return array<string, string>
	 */
	private function getActiveIntegrationIcons(string $id): array
	{
		$integrationTypeUsed = Helper::getUsedFormTypeById($id);

		if (!$integrationTypeUsed) {
			return [];
		}

		return [
			'label' => Filters::getSettingsLabels($integrationTypeUsed, 'title'),
			'icon' => Filters::ALL[$integrationTypeUsed]['icon'] ?? '',
			'value' => $integrationTypeUsed,
		];
	}
}
