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
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Plugin\HasActivationInterface;

/**
 * The plugin activation class.
 */
class Activate implements HasActivationInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Activate the plugin.
	 */
	public function activate(): void
	{
		// Add caps.
		$role = get_role('administrator');

		if ($role instanceof \WP_Role) {
			$role->add_cap(Forms::POST_CAPABILITY_TYPE);
			$role->add_cap(FormAdminMenu::ADMIN_MENU_CAPABILITY);
			$role->add_cap(FormGlobalSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY);
			$role->add_cap(FormListingAdminSubMenu::ADMIN_MENU_CAPABILITY);
			$role->add_cap(FormSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY);
		}

		// Create new tables.
		$this->createDbTables();

		// Do a cleanup.
		\flush_rewrite_rules();
	}
}
