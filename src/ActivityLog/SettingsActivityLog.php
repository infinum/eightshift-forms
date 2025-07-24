<?php

/**
 * Activity log settings class.
 *
 * @package EightshiftForms\ActivityLog
 */

declare(strict_types=1);

namespace EightshiftForms\ActivityLog;

use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsOutputHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Settings\UtilsSettingGlobalInterface;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * SettingsActivityLog class.
 */
class SettingsActivityLog implements UtilsSettingGlobalInterface, ServiceInterface
{
	/**
	 * Filter settings key.
	 */
	public const FILTER_SETTINGS_NAME = 'es_forms_settings_activity_log';

	/**
	 * Filter global settings key.
	 */
	public const FILTER_SETTINGS_GLOBAL_NAME = 'es_forms_settings_global_activity_log';

	/**
	 * Filter settings global is Valid key.
	 */
	public const FILTER_SETTINGS_GLOBAL_IS_VALID_NAME = 'es_forms_settings_global_is_valid_activity_log';

	/**
	 * Settings key.
	 */
	public const SETTINGS_TYPE_KEY = 'activity-log';

	/**
	 * Entries use key.
	 */
	public const SETTINGS_ACTIVITY_LOG_USE_KEY = 'activity-log-use';

	/**
	 * Entries settings Use key.
	 */
	public const SETTINGS_ACTIVITY_LOG_SETTINGS_USE_KEY = 'activity-log-settings-use';

	/**
	 * Save empty fields key.
	 */
	public const SETTINGS_ACTIVITY_LOG_SAVE_EMPTY_FIELDS = 'activity-log-save-empty-fields';

	/**
	 * Data data key.
	 */
	public const SETTINGS_ACTIVITY_LOG_DATA_KEY = 'activity-log-data';

	/**
	 * Rate limit key.
	 */
	public const SETTINGS_ACTIVITY_LOG_RATE_LIMIT_KEY = 'activity-log-rate-limit';

	/**
	 * Rate limit window key.
	 */
	public const SETTINGS_ACTIVITY_LOG_RATE_LIMIT_WINDOW_KEY = 'activity-log-rate-limit-window';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::FILTER_SETTINGS_NAME, [$this, 'getSettingsData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_NAME, [$this, 'getSettingsGlobalData']);
		\add_filter(self::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, [$this, 'isSettingsGlobalValid']);
	}

	/**
	 * Determine if settings global are valid.
	 *
	 * @return boolean
	 */
	public function isSettingsGlobalValid(): bool
	{
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_ACTIVITY_LOG_USE_KEY, self::SETTINGS_ACTIVITY_LOG_USE_KEY)) {
			return false;
		}

		return true;
	}

	/**
	 * Get global settings array for building settings page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getSettingsGlobalData(): array
	{
		if (!UtilsSettingsHelper::isOptionCheckboxChecked(self::SETTINGS_ACTIVITY_LOG_USE_KEY, self::SETTINGS_ACTIVITY_LOG_USE_KEY)) {
			return UtilsSettingsOutputHelper::getNoActiveFeature();
		}

		return [
			UtilsSettingsOutputHelper::getIntro(self::SETTINGS_TYPE_KEY),
			[
				'component' => 'tabs',
				'tabsContent' => [
					[
						'component' => 'tab',
						'tabLabel' => \__('Internal storage', 'eightshift-forms'),
						'tabContent' => [
							[
								'component' => 'intro',
								'introSubtitle' => \__('Activity log collection will allow you to store every form submission into the database and preview the data from WordPress admin.', 'eightshift-forms'),
							],
							[
								'component' => 'layout',
								'layoutType' => 'layout-v-stack-clean',
								'layoutContent' => [
									[
										'component' => 'card-inline',
										'cardInlineTitle' => \__('View all activity logs in database', 'eightshift-forms'),
										'cardInlineRightContent' => [
											[
												'component' => 'submit',
												'submitVariant' => 'ghost',
												'submitButtonAsLink' => true,
												'submitButtonAsLinkUrl' => UtilsGeneralHelper::getListingPageUrl(UtilsConfig::SLUG_ADMIN_LISTING_ACTIVITY_LOGS),
												'submitValue' => \__('View all activity logs', 'eightshift-forms'),
											],
										],
									],
								],
							]
						],
					],
				],
			],
		];
	}
}
