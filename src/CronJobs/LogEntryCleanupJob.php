<?php

/**
 * A cleanup service for log entries.
 *
 * @package EightshiftForms\CronJobs
 */

declare(strict_types=1);

namespace EightshiftForms\CronJobs;

use EightshiftForms\Security\RateLimitingLogEntry;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * A log entry cleanup service.
 */
class LogEntryCleanupJob implements ServiceInterface
{
	public const LOG_ENTRY_CLEANUP_ACTION = 'es_forms_cleanup_log_entries';

	/**
	 * Register all the hooks.
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('admin_init', [$this, 'maybeCleanupLogEntries']);
		\add_filter('cron_schedules', [$this, 'addJobToSchedule']); // phpcs:ignore WordPress.WP.CronInterval.ChangeDetected
		\add_action(self::LOG_ENTRY_CLEANUP_ACTION, [$this, 'cleanupLogEntries']);
	}

	/**
	 * Cleans up log entries using a recurring action if available, or immediately if not.
	 * Recurring actions are scheduled using Action Scheduler if available and run daily.
	 *
	 * @return void
	 */
	public function maybeCleanupLogEntries()
	{
		if (\function_exists('as_schedule_recurring_action')) {
			\as_schedule_recurring_action(
				\time(),
				\DAY_IN_SECONDS,
				self::LOG_ENTRY_CLEANUP_ACTION
			);

			return;
		}
		else {
			if (!\wp_next_scheduled(self::LOG_ENTRY_CLEANUP_ACTION)) {
				\wp_schedule_event(
					\strtotime('tomorrow', \time()),
					'daily',
					self::LOG_ENTRY_CLEANUP_ACTION
				);
			}
		}

		$this->cleanupLogEntries();
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
	 * Cleans up log entries older than a day.
	 *
	 * @return void
	 */
	public function cleanupLogEntries()
	{
		RateLimitingLogEntry::cleanup(\DAY_IN_SECONDS);
	}
}
