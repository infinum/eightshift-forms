<?php

/**
 * Class that holds all filter used the Block Editor page.
 *
 * @package EightshiftLibs\Editor
 */

declare(strict_types=1);

namespace EightshiftForms\Editor;

use EightshiftForms\AdminMenus\FormAdminMenu;
use EightshiftForms\CustomPostType\Forms;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Editor class.
 */
class Editor implements ServiceInterface
{
	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter('admin_init', [$this, 'getEditorBackLink']);
	}

	/**
	 * Create back link for editor.
	 *
	 * @return void
	 */
	public function getEditorBackLink(): void
	{
		$request = isset($_SERVER['REQUEST_URI']) ? \sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$postType = Forms::POST_TYPE_SLUG;
		$page = FormAdminMenu::ADMIN_MENU_SLUG;

		$links = [
			"/wp-admin/edit.php?post_type={$postType}",
			"/wp-admin/edit.php?post_status=publish&post_type={$postType}",
			"/wp-admin/edit.php?post_status=draft&post_type={$postType}",
			"/wp-admin/edit.php?post_status=trash&post_type={$postType}",
			"/wp-admin/edit.php?post_status=publish&post_type={$postType}",
			"/wp-admin/edit.php?post_status=future&post_type={$postType}",
		];

		if (in_array($request, $links, true)) {
			wp_safe_redirect("/wp-admin/admin.php?page={$page}");
			exit;
		}
	}
}
