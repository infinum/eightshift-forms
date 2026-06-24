<?php

/**
 * A cleanup service for log entries.
 *
 * @package EightshiftForms\CronJobs
 */

declare(strict_types=1);

namespace EightshiftForms\CronJobs;

use EightshiftForms\Security\RateLimitingLogEntry;
use EightshiftForms\Security\SettingsSecurity;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * A log entry cleanup service.
 */
class LogEntryCleanupJob implements ServiceInterface
{
	/**
	 * Job name.
	 *
	 * @var string
	 */
	public const string JOB_NAME = 'es_forms_cleanup_log_entries';

	/**
	 * Register all the hooks
	 */
	public function register(): void
	{
		\add_action('init', [$this, 'checkIfJobIsSet']);
		\add_action(self::JOB_NAME, [$this, 'getJobCallback']);
	}

	/**
	 * Check if job is set and add it if not.
	 */
	public function checkIfJobIsSet(): void
	{
		if (!\wp_next_scheduled(self::JOB_NAME)) {
			\wp_schedule_event(
				\strtotime('tomorrow', \time()),
				CronJobsSchedules::CRON_JOBS_SCHEDULE_EVERY_DAY,
				self::JOB_NAME
			);
		}
	}

	/**
	 * Run callback when event is triggered.
	 */
	public function getJobCallback(): void
	{
		if (!\apply_filters(SettingsSecurity::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			return;
		}

		RateLimitingLogEntry::cleanup(\DAY_IN_SECONDS);
	}
}
