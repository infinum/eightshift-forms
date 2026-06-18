<?php

/**
 * The file that defines actions on plugin deactivation.
 *
 * @package EightshiftForms
 */

declare(strict_types=1);

namespace EightshiftForms;

use EightshiftForms\Config\Config;
use EightshiftForms\CronJobs\ActivityLogAutoDeleteJob;
use EightshiftForms\CronJobs\ClearbitJob;
use EightshiftForms\CronJobs\EntriesAutoDeleteJob;
use EightshiftForms\CronJobs\FileUploadJob;
use EightshiftForms\CronJobs\LogEntryCleanupJob;
use EightshiftForms\CronJobs\NationbuilderJob;
use EightshiftForms\CronJobs\OauthCleanupJob;
use EightshiftForms\Permissions\Permissions;
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
				foreach (Config::CAPS as $item) {
					$role->remove_cap($item);
				}
			}
		}

		// Delete transients.
		foreach (\apply_filters(Config::FILTER_SETTINGS_DATA, []) as $items) {
			$cache = $items['cache'] ?? [];

			if (!$cache) {
				continue;
			}

			foreach ($cache as $item) {
				\delete_transient($item);
			}
		}

		// Remove cron jobs.
		\wp_clear_scheduled_hook(ActivityLogAutoDeleteJob::JOB_NAME);
		\wp_clear_scheduled_hook(ClearbitJob::JOB_NAME);
		\wp_clear_scheduled_hook(EntriesAutoDeleteJob::JOB_NAME);
		\wp_clear_scheduled_hook(FileUploadJob::JOB_NAME);
		\wp_clear_scheduled_hook(LogEntryCleanupJob::JOB_NAME);
		\wp_clear_scheduled_hook(NationbuilderJob::JOB_NAME);
		\wp_clear_scheduled_hook(OauthCleanupJob::JOB_NAME);

		// Do a cleanup.
		\flush_rewrite_rules();
	}
}
