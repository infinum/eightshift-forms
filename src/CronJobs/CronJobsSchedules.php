<?php

/**
 * Class that holds WP Cron job schedule events.
 *
 * @package EightshiftForms\CronJobs
 */

declare(strict_types=1);

namespace EightshiftForms\CronJobs;

use EightshiftFormsVendor\EightshiftLibs\Services\ServiceCliInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * CronJobsSchedules class.
 */
class CronJobsSchedules implements ServiceInterface, ServiceCliInterface
{
	/**
	 * Cron job schedule for every hour.
	 */
	public const CRON_JOBS_SCHEDULE_EVERY_HOUR = 'esFormsEveryHour';

	/**
	 * Cron job schedule for every 15 minutes.
	 */
	public const CRON_JOBS_SCHEDULE_EVERY_15_MINUTES = 'esFormsEvery15Minutes';

	/**
	 * Cron job schedule for every day.
	 */
	public const CRON_JOBS_SCHEDULE_EVERY_DAY = 'esFormsEveryDay';

	/**
	 * Register all the hooks
	 */
	public function register(): void
	{
		\add_filter('cron_schedules', $this->addJobToSchedule(...)); // phpcs:ignore WordPress.WP.CronInterval.CronSchedulesInterval
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
		$schedules[self::CRON_JOBS_SCHEDULE_EVERY_HOUR] = [
			'interval' => \HOUR_IN_SECONDS,
			'display' => \esc_html__('Every hour', 'eightshift-forms'),
		];

		$schedules[self::CRON_JOBS_SCHEDULE_EVERY_15_MINUTES] = [
			'interval' => \MINUTE_IN_SECONDS * 15,
			'display' => \esc_html__('Every 15 minutes', 'eightshift-forms'),
		];

		$schedules[self::CRON_JOBS_SCHEDULE_EVERY_DAY] = [
			'interval' => \DAY_IN_SECONDS,
			'display' => \esc_html__('Once a day', 'eightshift-forms'),
		];

		return $schedules;
	}
}
