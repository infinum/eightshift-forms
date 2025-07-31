<?php

/**
 * Class that holds WP Cron job schedule event for - NationbuilderJob.
 *
 * @package EightshiftForms\CronJobs
 */

declare(strict_types=1);

namespace EightshiftForms\CronJobs;

use EightshiftForms\Integrations\Nationbuilder\NationbuilderClientInterface;
use EightshiftForms\Integrations\Nationbuilder\SettingsNationbuilder;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Integrations\Mailer\MailerInterface;
use EightshiftForms\Troubleshooting\SettingsFallback;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceCliInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * NationbuilderJob class.
 */
class NationbuilderJob implements ServiceInterface, ServiceCliInterface
{
	/**
	 * Job name.
	 *
	 * @var string
	 */
	public const JOB_NAME = 'es_forms_nationbuilder_queue';

	/**
	 * Instance variable for NationbuilderClientInterface data.
	 *
	 * @var NationbuilderClientInterface
	 */
	protected $nationbuilderClient;

	/**
	 * Instance variable of MailerInterface data.
	 *
	 * @var MailerInterface
	 */
	public $mailer;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param MailerInterface $mailer Inject MailerInterface which holds mailer methods.
	 * @param NationbuilderClientInterface $nationbuilderClient Inject NationbuilderClientInterface methods.
	 */
	public function __construct(
		MailerInterface $mailer,
		NationbuilderClientInterface $nationbuilderClient
	) {
		$this->mailer = $mailer;
		$this->nationbuilderClient = $nationbuilderClient;
	}

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		if (!\apply_filters(SettingsNationbuilder::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			return;
		}

		\add_action('admin_init', [$this, 'checkIfJobIsSet']);
		\add_filter('cron_schedules', [$this, 'addJobToSchedule']); // phpcs:ignore WordPress.WP.CronInterval.CronSchedulesInterval
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
				\time(),
				'esFormsEvery5Minutes',
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
		$schedules['esFormsEvery5Minutes'] = [
			'interval' => \MINUTE_IN_SECONDS * 5,
			'display' => \esc_html__('Every 5 minutes', 'eightshift-forms'),
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
		$use = \apply_filters(SettingsNationbuilder::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false);
		$jobs = SettingsHelpers::getOptionValueGroup(SettingsNationbuilder::SETTINGS_NATIONBUILDER_CRON_KEY);

		if (!$use || !$jobs) {
			return;
		}

		foreach ($jobs as $type => $job) {
			if ($type === 'list') {
				foreach ($job as $listId => $signupIds) {
					foreach ($signupIds as $signupId) {
						$listResponse = $this->nationbuilderClient->postList((string) $listId, $signupId);

						if (ApiHelpers::isErrorResponse($listResponse[Config::IARD_CODE])) {
							$formDetails[Config::FD_RESPONSE_OUTPUT_DATA] = $listResponse;

							if (\apply_filters(SettingsFallback::FILTER_SETTINGS_SHOULD_LOG_ACTIVITY_NAME, false, SettingsFallback::SETTINGS_FALLBACK_FLAG_NATIONBUILDER_LIST_ERROR)) {
								$this->mailer->sendTroubleshootingEmail(
									$formDetails,
									[
										'response' => $listResponse[Config::IARD_RESPONSE] ?? [],
										'body' => $listResponse[Config::IARD_BODY] ?? [],
									],
									SettingsFallback::SETTINGS_FALLBACK_FLAG_NATIONBUILDER_LIST_ERROR
								);
							}
						}
					}

					unset($jobs[$type][$listId]);
				}
			}

			if ($type === 'tags') {
				foreach ($job as $tagId => $signupIds) {
					foreach ($signupIds as $signupId) {
						$tagResponse = $this->nationbuilderClient->postTag((string) $tagId, $signupId);

						if (ApiHelpers::isErrorResponse($tagResponse[Config::IARD_CODE])) {
							$formDetails[Config::FD_RESPONSE_OUTPUT_DATA] = $tagResponse;

							if (\apply_filters(SettingsFallback::FILTER_SETTINGS_SHOULD_LOG_ACTIVITY_NAME, false, SettingsFallback::SETTINGS_FALLBACK_FLAG_NATIONBUILDER_TAGS_ERROR)) {
								$this->mailer->sendTroubleshootingEmail(
									$formDetails,
									[
										'response' => $tagResponse[Config::IARD_RESPONSE] ?? [],
										'body' => $tagResponse[Config::IARD_BODY] ?? [],
									],
									SettingsFallback::SETTINGS_FALLBACK_FLAG_NATIONBUILDER_TAGS_ERROR
								);
							}
						}
					}

					unset($jobs[$type][$tagId]);
				}
			}
		}

		\update_option(SettingsHelpers::getOptionName(SettingsNationbuilder::SETTINGS_NATIONBUILDER_CRON_KEY), $jobs);

		// Turn of OAuth after cron job is done.
		\delete_option(SettingsHelpers::getOptionName(SettingsNationbuilder::SETTINGS_NATIONBUILDER_OAUTH_ALLOW_KEY));
	}
}
