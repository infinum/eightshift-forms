<?php

/**
 * The file that defines actions on plugin activation.
 *
 * @package EightshiftForms
 */

declare(strict_types=1);

namespace EightshiftForms;

use EightshiftForms\AdminMenus\FormAdminMenu;
use EightshiftForms\AdminMenus\FormGlobalSettingsAdminSubMenu;
use EightshiftForms\AdminMenus\FormListingAdminSubMenu;
use EightshiftForms\AdminMenus\FormSettingsAdminSubMenu;
use EightshiftForms\CustomPostType\Forms;
use EightshiftFormsVendor\EightshiftLibs\Plugin\HasActivationInterface;

/**
 * The plugin activation class.
 */
class Activate implements HasActivationInterface
{
	/**
	 * Activate the plugin.
	 */
	public function activate(): void
	{
		// Add caps.
		$role = get_role('administrator');

		$role->add_cap(Forms::POST_CAPABILITY_TYPE);
		$role->add_cap(FormAdminMenu::ADMIN_MENU_CAPABILITY);
		$role->add_cap(FormGlobalSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY);
		$role->add_cap(FormListingAdminSubMenu::ADMIN_MENU_CAPABILITY);
		$role->add_cap(FormSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY);

		// Do a cleanup.
		\flush_rewrite_rules();
	}
}
