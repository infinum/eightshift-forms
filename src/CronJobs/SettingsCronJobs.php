<?php

/**
 * Settings Cron Jobs class.
 *
 * @package EightshiftForms\CronJobs
 */

declare(strict_types=1);

namespace EightshiftForms\CronJobs;

use EightshiftForms\Helpers\SettingsOutputHelpers;
use EightshiftForms\Settings\SettingGlobalInterface;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsCronJobs class.
 */
class SettingsCronJobs implements SettingGlobalInterface, ServiceInterface
{
	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_cron';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'cron';

	/**
	 * Cron jobs list.
	 */
	public const JOBS = [
		ActivityLogAutoDeleteJob::JOB_NAME,
		ClearbitJob::JOB_NAME,
		EntriesAutoDeleteJob::JOB_NAME,
		FileUploadJob::JOB_NAME,
		LogEntryCleanupJob::JOB_NAME,
		NationbuilderJob::JOB_NAME,
		OauthCleanupJob::JOB_NAME,
	];

	/**
	 * Register all the hooks
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, $this->getSettingsGlobalData(...));
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		$data = self::JOBS;

		$outputIntegrations = \array_values(\array_filter(\array_map(
			fn(string $value): array => [
				'component' => 'card-inline',
				'cardInlineTitle' => $value,
				'cardInlineRightContent' => [
					[
						'component' => 'button',
						'buttonLabel' => \__('Clear', 'eightshift-forms'),
						'buttonVariant' => 'primaryGhost',
						'buttonAttrs' => [
							UtilsHelper::getStateAttribute('cronType') => $value,
							UtilsHelper::getStateAttribute('reload') => 'false',
						],
						'additionalClass' => UtilsHelper::getStateSelectorAdmin('cronRun'),
					],
				],
			],
			$data
		)));

		return [
			SettingsOutputHelpers::getIntro(self::SETTINGS_TYPE_KEY),
			...($outputIntegrations !== [] ? [
				[
					'component' => 'intro',
					'introTitle' => \__('Integration cache', 'eightshift-forms'),
					'introSubtitle' => \__('Here you can clear individual cache for each integration.', 'eightshift-forms'),
				],
				[
					'component' => 'layout',
					'layoutContent' => $outputIntegrations,
				],
			] : []),
		];
	}
}
