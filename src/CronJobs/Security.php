<?php

/**
 * Class that holds WP Cron job schedule event for - Security.
 *
 * @package EightshiftForms\CronJobs
 */

declare(strict_types=1);

namespace EightshiftForms\CronJobs;

use EightshiftForms\Security\SettingsSecurity;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Security class.
 */
class Security implements ServiceInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Job name.
	 *
	 * @var string
	 */
	public const JOB_NAME = 'es_forms_security';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('admin_init', [$this, 'checkIfJobIsSet']);
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
	public function getJobCallback()
	{
		\delete_option($this->getOptionName(SettingsSecurity::SETTINGS_SECURITY_DATA_KEY));
	}
}
