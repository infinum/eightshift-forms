<?php

/**
 * File that holds class for admin content listing.
 *
 * @package EightshiftLibs\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Settings;

use EightshiftForms\AdminMenus\FormOptionAdminSubMenu;
use EightshiftForms\CustomPostType\Forms;

/**
 * FormOptions class.
 */
class FormOptions implements FormOptionsInterface
{

	/**
	 * Get Form List items.
	 *
	 * @return array
	 */
	public function getFormsList(): array
	{
		$postType = Forms::POST_TYPE_SLUG;
		$optionPageSlug = FormOptionAdminSubMenu::ADMIN_MENU_SLUG;

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
					'settingsLink' => "/wp-admin/edit.php?post_type={$postType}&page={$optionPageSlug}&id={$id}",
					'editLink' => "/wp-admin/post.php?post={$id}&action=edit",
				];
			}
		}

		return $output;
	}
}
