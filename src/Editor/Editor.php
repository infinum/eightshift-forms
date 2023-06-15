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
use EightshiftForms\Helpers\Helper;
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
		$actialUrl = Helper::getCurrentUrl();
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
