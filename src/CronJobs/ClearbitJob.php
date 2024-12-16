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
use EightshiftForms\Integrations\Hubspot\SettingsHubspot;
use EightshiftForms\Rest\Routes\Integrations\Mailer\FormSubmitMailerInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * ClearbitJob class.
 */
class ClearbitJob implements ServiceInterface
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
	 * Instance variable of FormSubmitMailerInterface data.
	 *
	 * @var FormSubmitMailerInterface
	 */
	public $formSubmitMailer;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param FormSubmitMailerInterface $formSubmitMailer Inject FormSubmitMailerInterface which holds mailer methods.
	 * @param ClearbitClientInterface $clearbitClient Inject Clearbit which holds clearbit connect data.
	 */
	public function __construct(
		FormSubmitMailerInterface $formSubmitMailer,
		ClearbitClientInterface $clearbitClient
	) {
		$this->formSubmitMailer = $formSubmitMailer;
		$this->clearbitClient = $clearbitClient;
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
		$use = \apply_filters(SettingsClearbit::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false);
		$useCron = UtilsSettingsHelper::isOptionCheckboxChecked(SettingsClearbit::SETTINGS_CLEARBIT_USE_JOBS_QUEUE_KEY, SettingsClearbit::SETTINGS_CLEARBIT_USE_JOBS_QUEUE_KEY);

		if (!$use || !$useCron) {
			return;
		}

		$jobs = UtilsSettingsHelper::getOptionValueGroup(SettingsClearbit::SETTINGS_CLEARBIT_JOBS_KEY);

		if (!$jobs) {
			return;
		}

		foreach ($jobs as $type => $job) {
			foreach ($job as $formId => $emails) {
				$isValid = \apply_filters(SettingsClearbit::FILTER_SETTINGS_IS_VALID_NAME, $formId, $type);

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
						UtilsSettingsHelper::getOptionValueGroup(SettingsClearbit::SETTINGS_CLEARBIT_MAP_HUBSPOT_KEYS_KEY . '-' . $type),
						(string) $formId
					);

					if ($clearbitResponse[UtilsConfig::IARD_CODE] >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $clearbitResponse[UtilsConfig::IARD_CODE] <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
						if ($type === SettingsHubspot::SETTINGS_TYPE_KEY) {
							\apply_filters(
								UtilsHooksHelper::getFilterName(['integrations', $type, 'postContactProperty']),
								[],
								$clearbitResponse['email'] ?? '',
								$clearbitResponse['data'] ?? []
							);
						}
					} else {
						// Send fallback email if error but ignore for unknown entry.
						if ($clearbitResponse[UtilsConfig::IARD_CODE] !== UtilsConfig::API_RESPONSE_CODE_ERROR_MISSING) {
							$formDetails[UtilsConfig::FD_RESPONSE_OUTPUT_DATA] = $clearbitResponse;

							$this->formSubmitMailer->sendFallbackIntegrationEmail($formDetails);
						}
					}

					unset($jobs[$type][$formId][$key]);

					\update_option(UtilsSettingsHelper::getOptionName(SettingsClearbit::SETTINGS_CLEARBIT_JOBS_KEY), $jobs);
				}
			}
		}
	}
}
