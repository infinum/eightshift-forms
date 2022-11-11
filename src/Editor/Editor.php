<?php

/**
 * Class that holds all filter used the Block Editor page.
 *
 * @package EightshiftForms\Editor
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
		\add_action('admin_head-edit.php', [$this, 'getEditorBackLink']);
	}

	/**
	 * Create back link for editor.
	 *
	 * @return void
	 */
	public function getEditorBackLink(): void
	{
		$port = isset($_SERVER['HTTPS']) ? \sanitize_text_field(\wp_unslash($_SERVER['HTTPS'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$host = isset($_SERVER['HTTP_HOST']) ? \sanitize_text_field(\wp_unslash($_SERVER['HTTP_HOST'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$request = isset($_SERVER['REQUEST_URI']) ? \sanitize_text_field(\wp_unslash($_SERVER['REQUEST_URI'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$actialUrl = ($port ? "https" : "http") . "://{$host}{$request}";

		if (\getenv('test_force_request_uri')) {
			$request = \getenv('test_force_request_uri');
		}

		$postType = Forms::POST_TYPE_SLUG;
		$page = FormAdminMenu::ADMIN_MENU_SLUG;

		$links = [
			\get_admin_url(null, "edit.php?post_type={$postType}"),
			\get_admin_url(null, "edit.php?post_status=publish&post_type={$postType}"),
			\get_admin_url(null, "edit.php?post_status=draft&post_type={$postType}"),
			\get_admin_url(null, "edit.php?post_status=trash&post_type={$postType}"),
			\get_admin_url(null, "edit.php?post_status=publish&post_type={$postType}"),
			\get_admin_url(null, "edit.php?post_status=future&post_type={$postType}"),
		];

		if (\in_array($actialUrl, $links, true)) {
			echo '<script>window.location.replace("' . \get_admin_url(null, "admin.php?page={$page}") . '");</script>'; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
		}
	}
}
