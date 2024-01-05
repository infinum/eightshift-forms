<?php

/**
 * The file that defines actions on plugin activation.
 *
 * @package EightshiftForms
 */

declare(strict_types=1);

namespace EightshiftForms;

use EightshiftForms\Permissions\Permissions;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
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
				foreach (UtilsConfig::CAPS as $item) {
					$role->add_cap($item);
				}
			}
		}

		// Do a cleanup.
		\flush_rewrite_rules();
	}
}
