<?php

/**
 * Class that holds WP Cron job schedule event for - EntriesAutoDelete.
 *
 * @package EightshiftForms\CronJobs
 */

declare(strict_types=1);

namespace EightshiftForms\CronJobs;

use DateTime;
use EightshiftForms\Entries\EntriesHelper;
use EightshiftForms\Entries\SettingsEntries;
use EightshiftForms\Listing\FormListingInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceCliInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * EntriesAutoDelete class.
 */
class EntriesAutoDelete implements ServiceInterface, ServiceCliInterface
{
	/**
	 * Job name.
	 *
	 * @var string
	 */
	public const JOB_NAME = 'es_forms_entries_auto_delete';

	/**
	 * Instance variable for listing data.
	 *
	 * @var FormListingInterface
	 */
	protected $formsListing;

	/**
	 * Create a new instance.
	 *
	 * @param FormListingInterface $formsListing Inject form listing data.
	 */
	public function __construct(FormListingInterface $formsListing)
	{
		$this->formsListing = $formsListing;
	}

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
		$forms = $this->formsListing->getFormsList();

		foreach ($forms as $form) {
			$formId = (string) $form['id'] ?? '';

			$autoDeleteIsUsed = UtilsSettingsHelper::isSettingCheckboxChecked(SettingsEntries::SETTINGS_ENTRIES_AUTO_DELETE_KEY, SettingsEntries::SETTINGS_ENTRIES_AUTO_DELETE_KEY, $formId);
			$retentionInterval = UtilsSettingsHelper::getSettingValue(SettingsEntries::SETTINGS_ENTRIES_AUTO_DELETE_RETENTION_KEY, $formId);

			if (!$autoDeleteIsUsed || !\is_numeric($retentionInterval) || (int) $retentionInterval < 1) {
				continue;
			}

			$entries = EntriesHelper::getEntries($formId);

			foreach ($entries as $entry) {
				$entryDate = $entry['createdAt'] ?? '';
				$entryDate = new DateTime($entryDate);
				$entryDate->modify('+' . $retentionInterval . ' days');

				// If entry date is older than retention interval, delete entry.
				if ($entryDate < new DateTime()) {
					$entryId = $entry['id'] ?? '';

					if ($entryId) {
						EntriesHelper::deleteEntry($entryId);
					}
				}
			}
		}
	}
}
