<?php

/**
 * File containing an user Permissions class.
 *
 * @package EightshiftForms\Permissions
 */

declare(strict_types=1);

namespace EightshiftForms\Permissions;

use EightshiftForms\AdminMenus\FormAdminMenu;
use EightshiftForms\AdminMenus\FormGlobalSettingsAdminSubMenu;
use EightshiftForms\AdminMenus\FormListingAdminSubMenu;
use EightshiftForms\AdminMenus\FormSettingsAdminSubMenu;
use EightshiftForms\CustomPostType\Forms;

/**
 * Class Permissions
 */
class Permissions
{
	/**
	 * Default user role to assign permissions.
	 */
	public const DEFAULT_MINIMAL_ROLES = [
		'editor',
		'administrator',
	];

	/**
	 * All permissions.
	 *
	 * @return array <string>
	 */
	static public function getPermissions(): array
	{
		$postType = Forms::POST_CAPABILITY_TYPE;

		return [
			$postType,
			FormAdminMenu::ADMIN_MENU_CAPABILITY,
			FormGlobalSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY,
			FormListingAdminSubMenu::ADMIN_MENU_CAPABILITY,
			FormSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY,
			"edit_{$postType}",
			"read_{$postType}",
			"delete_{$postType}",
			"edit_{$postType}s",
			"edit_others_{$postType}s",
			"delete_{$postType}s",
			"publish_{$postType}s",
			"read_private_{$postType}s",
		];
	}
}
