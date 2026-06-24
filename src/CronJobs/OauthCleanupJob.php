<?php

/**
 * Class that holds WP Cron job schedule event for - OauthCleanupJob.
 *
 * @package EightshiftForms\CronJobs
 */

declare(strict_types=1);

namespace EightshiftForms\CronJobs;

use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Integrations\Nationbuilder\SettingsNationbuilder;
use EightshiftForms\Integrations\Pardot\SettingsPardot;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceCliInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * OauthCleanupJob class.
 */
class OauthCleanupJob implements ServiceInterface, ServiceCliInterface
{
	/**
	 * Job name.
	 *
	 * @var string
	 */
	public const JOB_NAME = 'es_forms_oauth_cleanup';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('admin_init', [$this, 'checkIfJobIsSet']);
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
				CronJobsSchedules::CRON_JOBS_SCHEDULE_EVERY_DAY,
				self::JOB_NAME
			);
		}
	}

	/**
	 * Run callback when event is triggered.
	 *
	 * @return void
	 */
	public function getJobCallback(): void
	{
		$oauthAllowKeys = [
			SettingsNationbuilder::SETTINGS_NATIONBUILDER_OAUTH_ALLOW_KEY,
			SettingsPardot::SETTINGS_PARDOT_OAUTH_ALLOW_KEY,
		];

		foreach ($oauthAllowKeys as $key) {
			\delete_option(SettingsHelpers::getOptionName($key));
		}
	}
}
