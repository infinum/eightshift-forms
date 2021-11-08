<?php

/**
 * The file that defines actions on plugin deactivation.
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
use EightshiftForms\Integrations\Greenhouse\GreenhouseClient;
use EightshiftForms\Integrations\Mailchimp\MailchimpClient;
use EightshiftFormsVendor\EightshiftLibs\Plugin\HasDeactivationInterface;

/**
 * The plugin deactivation class.
 */
class Deactivate implements HasDeactivationInterface
{
	/**
	 * Deactivate the plugin.
	 */
	public function deactivate(): void
	{
		// Remove caps.
		$role = get_role('administrator');

		if ($role instanceof \WP_Role) {
			$role->remove_cap(Forms::POST_CAPABILITY_TYPE);
			$role->remove_cap(FormAdminMenu::ADMIN_MENU_CAPABILITY);
			$role->remove_cap(FormGlobalSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY);
			$role->remove_cap(FormListingAdminSubMenu::ADMIN_MENU_CAPABILITY);
			$role->remove_cap(FormSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY);
		}

		// Delet transients.
		delete_transient(MailchimpClient::CACHE_MAILCHIMP_ITEMS_TRANSIENT_NAME);
		delete_transient(MailchimpClient::CACHE_MAILCHIMP_ITEM_TRANSIENT_NAME);
		delete_transient(GreenhouseClient::CACHE_GREENHOUSE_ITEMS_TRANSIENT_NAME);
		delete_transient(GreenhouseClient::CACHE_GREENHOUSE_ITEM_TRANSIENT_NAME);

		// Do a cleanup.
		\flush_rewrite_rules();
	}
}
