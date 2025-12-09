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
	 *
	 * @return void
	 */
	public function register(): void
	{
		if (!\apply_filters(SettingsSecurity::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			return;
		}

		\add_action('init', [$this, 'checkIfJobIsSet']);
		\add_filter('cron_schedules', [$this, 'addJobToSchedule']); // phpcs:ignore WordPress.WP.CronInterval.ChangeDetected
		\add_action(self::JOB_NAME, [$this, 'getJobCallback']);
	}

	/**
	 * Check if job is set and add it if not.
	 *
	 * @return void
	 */
	public function checkIfJobIsSet(): void
	{
		if (!\wp_next_scheduled(self::JOB_NAME)) {
			\wp_schedule_event(
				\strtotime('tomorrow', \time()),
				'daily',
				self::JOB_NAME
			);
		}
	}

	/**
	 * Add job to schedule.
	 *
	 * @param array<mixed> $schedules WP schedules list.
	 *
	 * @return array<mixed>
	 */
	public function addJobToSchedule(array $schedules): array
	{
		$schedules['daily'] = [
			'interval' => \DAY_IN_SECONDS,
			'display' => \esc_html__('Every day at midnight', 'eightshift-forms'),
		];

		return $schedules;
	}

	/**
	 * Run callback when event is triggered.
	 *
	 * @return void
	 */
	public function getJobCallback(): void
	{
		RateLimitingLogEntry::cleanup(\DAY_IN_SECONDS);
	}
}
