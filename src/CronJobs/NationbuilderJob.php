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
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
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
	 * Instance variable of FormSubmitMailerInterface data.
	 *
	 * @var FormSubmitMailerInterface
	 */
	public $formSubmitMailer;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject FormSubmitMailerInterface which holds mailer methods.
	 * @param NationbuilderClientInterface $nationbuilderClient Inject NationbuilderClientInterface methods.
	 */
	public function __construct(
		FormSubmitMailerInterface $formSubmitMailer,
		NationbuilderClientInterface $nationbuilderClient
	) {
		$this->formSubmitMailer = $formSubmitMailer;
		$this->nationbuilderClient = $nationbuilderClient;
	}

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
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
		$jobs = UtilsSettingsHelper::getOptionValueGroup(SettingsNationbuilder::SETTINGS_NATIONBUILDER_CRON_KEY);

		if (!$use || !$jobs) {
			return;
		}

		foreach ($jobs as $type => $job) {
			if ($type === 'list') {
				foreach ($job as $listId => $signupIds) {
					foreach ($signupIds as $signupId) {
						$listResponse = $this->nationbuilderClient->postList((string) $listId, $signupId);

						if ($listResponse[UtilsConfig::IARD_CODE] < UtilsConfig::API_RESPONSE_CODE_SUCCESS && $listResponse[UtilsConfig::IARD_CODE] > UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
							$formDetails[UtilsConfig::FD_RESPONSE_OUTPUT_DATA] = $listResponse;

							$this->formSubmitMailer->sendFallbackIntegrationEmail($formDetails);
						}
					}

					unset($jobs[$type][$listId]);
				}
			}

			if ($type === 'tags') {
				foreach ($job as $tagId => $signupIds) {
					foreach ($signupIds as $signupId) {
						$tagResponse = $this->nationbuilderClient->postTag((string) $tagId, $signupId);

						if ($tagResponse[UtilsConfig::IARD_CODE] < UtilsConfig::API_RESPONSE_CODE_SUCCESS && $tagResponse[UtilsConfig::IARD_CODE] > UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
							$formDetails[UtilsConfig::FD_RESPONSE_OUTPUT_DATA] = $tagResponse;

							$this->formSubmitMailer->sendFallbackIntegrationEmail($formDetails);
						}
					}

					unset($jobs[$type][$tagId]);
				}
			}
		}

		\update_option(UtilsSettingsHelper::getOptionName(SettingsNationbuilder::SETTINGS_NATIONBUILDER_CRON_KEY), $jobs);

		// Turn of OAuth after cron job is done.
		\delete_option(UtilsSettingsHelper::getOptionName(SettingsNationbuilder::SETTINGS_NATIONBUILDER_OAUTH_ALLOW_KEY));
	}
}
