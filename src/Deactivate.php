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

		$role->remove_cap(Forms::POST_CAPABILITY_TYPE);
		$role->remove_cap(FormAdminMenu::ADMIN_MENU_CAPABILITY);
		$role->remove_cap(FormGlobalSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY);
		$role->remove_cap(FormListingAdminSubMenu::ADMIN_MENU_CAPABILITY);
		$role->remove_cap(FormSettingsAdminSubMenu::ADMIN_MENU_CAPABILITY);

		// Delet transients.
		delete_transient(MailchimpClient::CACHE_MAILCHIMP_LISTS_TRANSIENT_NAME);
		delete_transient(MailchimpClient::CACHE_MAILCHIMP_LIST_FIELDS_TRANSIENT_NAME);
		delete_transient(GreenhouseClient::CACHE_GREENHOUSE_JOBS_TRANSIENT_NAME);
		delete_transient(GreenhouseClient::CACHE_GREENHOUSE_JOBS_QUESTIONS_TRANSIENT_NAME);

		// Do a cleanup.
		\flush_rewrite_rules();
	}
}
