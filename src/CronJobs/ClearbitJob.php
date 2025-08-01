<?php

/**
 * Class that holds WP Cron job schedule event for - ClearbitJob.
 *
 * @package EightshiftForms\CronJobs
 */

declare(strict_types=1);

namespace EightshiftForms\CronJobs;

use EightshiftForms\Integrations\Clearbit\ClearbitClientInterface;
use EightshiftForms\Integrations\Clearbit\SettingsClearbit;
use EightshiftForms\Integrations\Hubspot\HubspotClientInterface;
use EightshiftForms\Integrations\Hubspot\SettingsHubspot;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Integrations\Mailer\MailerInterface;
use EightshiftForms\Troubleshooting\SettingsFallback;
use EightshiftFormsVendor\EightshiftLibs\Rest\Routes\AbstractRoute;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceCliInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * ClearbitJob class.
 */
class ClearbitJob implements ServiceInterface, ServiceCliInterface
{
	/**
	 * Job name.
	 *
	 * @var string
	 */
	public const JOB_NAME = 'es_forms_clearbit_queue';

	/**
	 * Instance variable for Clearbit data.
	 *
	 * @var ClearbitClientInterface
	 */
	protected $clearbitClient;

	/**
	 * Instance variable for HubSpot data.
	 *
	 * @var HubspotClientInterface
	 */
	protected $hubspotClient;

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
	 * @param ClearbitClientInterface $clearbitClient Inject Clearbit which holds clearbit connect data.
	 * @param HubspotClientInterface $hubspotClient Inject Hubspot which holds hubspot connect data.
	 */
	public function __construct(
		MailerInterface $mailer,
		ClearbitClientInterface $clearbitClient,
		HubspotClientInterface $hubspotClient
	) {
		$this->mailer = $mailer;
		$this->clearbitClient = $clearbitClient;
		$this->hubspotClient = $hubspotClient;
	}

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		if (!\apply_filters(SettingsClearbit::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
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
		$use = \apply_filters(SettingsClearbit::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false);
		$jobs = SettingsHelpers::getOptionValueGroup(SettingsClearbit::SETTINGS_CLEARBIT_CRON_KEY);

		if (!$use || !$jobs) {
			return;
		}

		foreach ($jobs as $type => $job) {
			foreach ($job as $formId => $emails) {
				$isValid = \apply_filters(SettingsClearbit::FILTER_SETTINGS_IS_VALID_NAME, false, $formId);

				if (!$isValid) {
					continue;
				}

				if (!$emails) {
					continue;
				}

				foreach ($emails as $key => $email) {
					// Get Clearbit data.
					$clearbitResponse = $this->clearbitClient->getApplication(
						$email,
						SettingsHelpers::getOptionValueGroup(SettingsClearbit::SETTINGS_CLEARBIT_MAP_HUBSPOT_KEYS_KEY . '-' . $type),
						(string) $formId
					);

					if (ApiHelpers::isSuccessResponse($clearbitResponse[Config::IARD_CODE])) {
						if ($type === SettingsHubspot::SETTINGS_TYPE_KEY) {
							$this->hubspotClient->postContactProperty(
								$clearbitResponse['email'] ?? '',
								$clearbitResponse['data'] ?? []
							);
						}
					} else {
						// Send fallback email if error but ignore for unknown entry.
						if ($clearbitResponse[Config::IARD_CODE] !== AbstractRoute::API_RESPONSE_CODE_NOT_FOUND) {
							$formDetails[Config::FD_RESPONSE_OUTPUT_DATA] = $clearbitResponse;

							if (\apply_filters(SettingsFallback::FILTER_SETTINGS_SHOULD_LOG_ACTIVITY_NAME, false, SettingsFallback::SETTINGS_FALLBACK_FLAG_CLEARBIT_CRON_ERROR)) {
								$this->mailer->sendTroubleshootingEmail(
									[
										Config::FD_FORM_ID => (string) $formId,
										Config::FD_TYPE => $type,
									],
									[
										'response' => $clearbitResponse[Config::IARD_RESPONSE] ?? [],
										'body' => $clearbitResponse[Config::IARD_BODY] ?? [],
									],
									SettingsFallback::SETTINGS_FALLBACK_FLAG_CLEARBIT_CRON_ERROR
								);
							}
						}
					}

					unset($jobs[$type][$formId][$key]);

					\update_option(SettingsHelpers::getOptionName(SettingsClearbit::SETTINGS_CLEARBIT_CRON_KEY), $jobs);
				}
			}
		}
	}
}
