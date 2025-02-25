<?php

/**
 * A cleanup service for log entries.
 *
 * @package EightshiftForms\Security
 */

declare(strict_types=1);

namespace EightshiftForms\Security;

use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * A log entry cleanup service.
 */
class LogEntryCleanupService implements ServiceInterface
{
	public const LOG_ENTRY_CLEANUP_ACTION = 'es_forms_cleanup_log_entries';

	/**
	 * Register all the hooks.
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('init', [$this, 'maybeCleanupLogEntries']);
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

		$this->cleanupLogEntries();
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
