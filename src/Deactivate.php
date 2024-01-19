<?php

/**
 * The file that defines actions on plugin deactivation.
 *
 * @package EightshiftForms
 */

declare(strict_types=1);

namespace EightshiftForms;

use EightshiftForms\CronJobs\FileUploadJob;
use EightshiftForms\Permissions\Permissions;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftLibs\Plugin\HasDeactivationInterface;
use WP_Role;

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
		foreach (Permissions::DEFAULT_MINIMAL_ROLES as $roleName) {
			$role = \get_role($roleName);

			if ($role instanceof WP_Role) {
				foreach (UtilsConfig::CAPS as $item) {
					$role->remove_cap($item);
				}
			}
		}

		// Delete transients.
		foreach (\apply_filters(UtilsConfig::FILTER_SETTINGS_DATA, []) as $items) {
			$cache = $items['cache'] ?? [];

			if (!$cache) {
				continue;
			}

			foreach ($cache as $item) {
				\delete_transient($item);
			}
		}

		// Remove cron job.
		\wp_clear_scheduled_hook(FileUploadJob::JOB_NAME);

		// Do a cleanup.
		\flush_rewrite_rules();
	}
}
