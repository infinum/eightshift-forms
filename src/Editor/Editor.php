<?php

/**
 * Class that holds all filter used the Block Editor page.
 *
 * @package EightshiftForms\Editor
 */

declare(strict_types=1);

namespace EightshiftForms\Editor;

use EightshiftForms\AdminMenus\FormAdminMenu;
use EightshiftForms\CustomPostType\Calculator;
use EightshiftForms\CustomPostType\Forms;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
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
		$actualUrl = UtilsGeneralHelper::getCurrentUrl();

		$types = [
			Forms::POST_TYPE_SLUG,
			Calculator::POST_TYPE_SLUG,
		];

		foreach ($types as $type) {
			$links = $this->getListOfLinks($type);

			$typeKey = $type === Forms::POST_TYPE_SLUG ? '' : 'calculator';

			if (\in_array($actualUrl, $links, true)) {
				echo '<script>window.location.replace("' . UtilsGeneralHelper::getListingPageUrl($typeKey) . '");</script>'; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
			}
		}
	}

	/**
	 * Get list of links.
	 *
	 * @param string $type Type of post.
	 *
	 * @return array
	 */
	private function getListOfLinks(string $type): array
	{
		return [
			\get_admin_url(null, "edit.php?post_type={$type}"),
			\get_admin_url(null, "edit.php?post_status=publish&post_type={$type}"),
			\get_admin_url(null, "edit.php?post_status=draft&post_type={$type}"),
			\get_admin_url(null, "edit.php?post_status=trash&post_type={$type}"),
			\get_admin_url(null, "edit.php?post_status=publish&post_type={$type}"),
			\get_admin_url(null, "edit.php?post_status=future&post_type={$type}"),
		];
	}
}
