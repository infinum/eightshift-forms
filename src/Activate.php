<?php

/**
 * The file that defines actions on plugin activation.
 *
 * @package EightshiftForms
 */

declare(strict_types=1);

namespace EightshiftForms;

use EightshiftForms\Db\CreateEntriesTable;
use EightshiftForms\Permissions\Permissions;
use EightshiftForms\Config\Config;
use EightshiftFormsVendor\EightshiftLibs\Plugin\HasActivationInterface;
use WP_Role;

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
		foreach (Permissions::DEFAULT_MINIMAL_ROLES as $roleName) {
			$role = \get_role($roleName);

			if ($role instanceof WP_Role) {
				foreach (Config::CAPS as $item) {
					$role->add_cap($item);
				}
			}
		}

		// Create DB table.
		CreateEntriesTable::createTable();

		// Do a cleanup.
		\flush_rewrite_rules();
	}
}
