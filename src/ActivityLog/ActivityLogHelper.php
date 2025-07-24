<?php

/**
 * ActivityLogHelper class.
 *
 * @package EightshiftForms\ActivityLog
 */

declare(strict_types=1);

namespace EightshiftForms\ActivityLog;

use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;

/**
 * ActivityLogHelper class.
 */
class ActivityLogHelper
{
	/**
	 * Table name.
	 *
	 * @var string
	 */
	public const TABLE_NAME = 'es_forms_activity_log';

	public const REQUEST_STATUS_INIT = 'init';
	public const REQUEST_STATUS_INIT_CAPTCHA_ERROR = 'initCaptchaError';
	public const REQUEST_STATUS_INIT_ERROR_LOGGED_IN = 'initErrorLoggedIn';
	public const REQUEST_STATUS_INIT_ERROR_SUBMIT_ONLY_ONCE = 'initErrorSubmitOnlyOnce';
	public const REQUEST_STATUS_INIT_ERROR_FALLBACK = 'initErrorFallback';
	public const REQUEST_STATUS_INIT_ERROR = 'initError';

	public const REQUEST_STATUS_BEFORE_INTEGRATION = 'beforeIntegration';
	public const REQUEST_STATUS_AFTER_INTEGRATION = 'afterIntegration';

	// /**
	//  * Get entry by form data reference.
	//  *
	//  * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	//  *
	//  * @return int|false
	//  */
	// public static function setEntryByFormDataRef(array $formDetails)
	// {
	// 	$params = \array_merge(
	// 		$formDetails[UtilsConfig::FD_PARAMS] ?? [],
	// 		$formDetails[UtilsConfig::FD_FILES] ?? []
	// 	);
	// 	$formId = $formDetails[UtilsConfig::IARD_FORM_ID] ?? '';

	// 	$output = [];

	// 	// Filter params.
	// 	$filterName = UtilsHooksHelper::getFilterName(['entries', 'prePostParams']);
	// 	if (\has_filter($filterName)) {
	// 		$params = \apply_filters($filterName, $params, $formId, $formDetails) ?? [];
	// 	}

	// 	// Get skipped params earlier as we are removing them from the main array using removeUneceseryParamFields method.
	// 	$paramsSkipped = FormsHelper::getParamValue(UtilsHelper::getStateParam('skippedParams'), $params);

	// 	$params = UtilsGeneralHelper::removeUneceseryParamFields($params);

	// 	$saveEmptyFields = UtilsSettingsHelper::isSettingCheckboxChecked(SettingsEntries::SETTINGS_ENTRIES_SAVE_EMPTY_FIELDS, SettingsEntries::SETTINGS_ENTRIES_SAVE_EMPTY_FIELDS, $formId);

	// 	foreach ($params as $param) {
	// 		$name = $param['name'] ?? '';
	// 		$value = $param['value'] ?? '';
	// 		$type = $param['type'] ?? '';

	// 		if (!$name || !$type) {
	// 			continue;
	// 		}

	// 		if (!$value && !$saveEmptyFields) {
	// 			continue;
	// 		}

	// 		if ($type === 'file') {
	// 			$value = \array_map(
	// 				static function (string $file) {
	// 					$filename = \pathinfo($file, \PATHINFO_FILENAME);
	// 					$extension = \pathinfo($file, \PATHINFO_EXTENSION);
	// 					return "{$filename}.{$extension}";
	// 				},
	// 				$value
	// 			);
	// 		}

	// 		$output[$name] = $value;
	// 	}

	// 	// Output skipped params as empty strings.
	// 	if ($paramsSkipped && $saveEmptyFields) {
	// 		foreach ($paramsSkipped as $key => $value) {
	// 			$output[$key] = '';
	// 		}
	// 	}

	// 	if (!$output) {
	// 		return false;
	// 	}

	// 	return self::setEntry($output, $formId);
	// }

	/**
	 * Get activity log admin URL.
	 *
	 * @param string $activityLogId Activity log Id.
	 * @param string $formId Form Id.
	 *
	 * @return string
	 */
	public static function getActivityLogAdminUrl(string $activityLogId, string $formId): string
	{
		return UtilsGeneralHelper::getListingPageUrl(UtilsConfig::SLUG_ADMIN_LISTING_ACTIVITY_LOGS, $formId) . "#activity-log-{$activityLogId}";
	}

	/**
	 * Get activity log by ID.
	 *
	 * @param string $id Activity log Id.
	 *
	 * @return array<string, mixed>
	 */
	public static function getActivityLog(string $id): array
	{
		global $wpdb;

		$tableName = self::getFullTableName();

		$output = \wp_cache_get($id, self::TABLE_NAME . 'activity_log');

		if (!$output) {
			$output = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->prepare(
					"SELECT * FROM {$tableName} WHERE id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					[
						(int) $id
					]
				),
				\ARRAY_A
			);

			\wp_cache_add($id, $output, self::TABLE_NAME . 'activity_log');
		}

		if (\is_wp_error($output) || !$output) {
			return [];
		}

		return self::prepareActivityLogOutput($output);
	}

	/**
	 * Get all activity logs.
	 *
	 * @return array<string, mixed>
	 */
	public static function getActivityLogsAll(): array
	{
		global $wpdb;

		$tableName = self::getFullTableName();

		$output = \wp_cache_get('all', self::TABLE_NAME . 'activity_logs');

		if (!$output) {
			$output = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				"SELECT * FROM {$tableName}", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				\ARRAY_A
			);

			\wp_cache_add('all', $output, self::TABLE_NAME . 'activity_logs');
		}

		if (\is_wp_error($output) || !$output) {
			return [];
		}

		foreach ($output as $key => $value) {
			$output[$key] = self::prepareActivityLogOutput($value);
		}

		return $output;
	}

	/**
	 * Get activity logs by form ID.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<mixed>
	 */
	public static function getActivityLogs(string $formId): array
	{
		global $wpdb;

		$tableName = self::getFullTableName();

		$output = \wp_cache_get($formId, self::TABLE_NAME . 'activity_logs');

		if (!$output) {
			$output = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->prepare(
					"SELECT * FROM {$tableName} WHERE form_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					[
						(int) $formId
					]
				),
				\ARRAY_A
			);

			\wp_cache_add($formId, $output, self::TABLE_NAME . 'activity_logs');
		}

		if (\is_wp_error($output) || !$output) {
			return [];
		}

		$results = [];

		foreach ($output as $value) {
			$results[] = self::prepareActivityLogOutput($value);
		}

		return $results;
	}

	/**
	 * Set activity log.
	 *
	 * @param array<string, mixed> $formData Form data to save.
	 * @param string $status Status of the activity log.
	 * @param string $ip IP address of the request.
	 *
	 * @return int|false
	 */
	public static function setActivityLog(array $formData, string $status, string $ip)
	{
		global $wpdb;

		$formId = $formData[UtilsConfig::IARD_FORM_ID] ?? '';

		if (!$formId) {
			return false;
		}

		$output = \wp_json_encode($formData);

		$time = \current_time('mysql', true);

		$result = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			self::getFullTableName(),
			[
				'form_id' => (int) $formId,
				'form_data' => $output,
				'request_status' => $status,
				'ip_address' => $ip,
				'created_at' => $time,
			],
			[
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
			]
		);

		if (\is_wp_error($result)) {
			return false;
		}

		return $wpdb->insert_id; //phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
	}

	/**
	 * Update activity log.
	 *
	 * @param array<string, mixed> $formData Form data to update.
	 * @param string $status Status of the activity log.
	 * @param int $id Entry Id.
	 *
	 * @return boolean
	 */
	public static function updateActivityLog(array $formData, string $status, int $id): bool
	{
		global $wpdb;

		$output = \wp_json_encode($formData);

		$result = $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			self::getFullTableName(),
			[
				'form_data' => $output,
				'request_status' => $status,
			],
			[
				'id' => (int) $id,
			],
			[
				'%s',
				'%s',
			],
			[
				'%d',
			]
		);

		if (\is_wp_error($result)) {
			return false;
		}

		return true;
	}

	/**
	 * Delete activity log.
	 *
	 * @param string $id Entry Id.
	 *
	 * @return boolean
	 */
	public static function deleteActivityLog(string $id): bool
	{
		global $wpdb;

		$tableName = self::getFullTableName();

		$result = $wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$tableName,
			[
				'id' => (int) $id,
			],
			[
				'%d',
			]
		);

		if (\is_wp_error($result)) {
			return false;
		}

		if (\wp_cache_get($id, self::TABLE_NAME . 'activity_log')) {
			\wp_cache_delete($id, self::TABLE_NAME . 'activity_log');
		}

		return true;
	}

	/**
	 * Prepare activity log output from DB.
	 *
	 * @param array<string, mixed> $data Data to prepare.
	 *
	 * @return array<string, mixed>
	 */
	private static function prepareActivityLogOutput(array $data): array
	{
		return [
			'id' => $data['id'] ?? '',
			'formId' => $data['form_id'] ?? '',
			'formData' => $data['form_data'] ?? '',
			'requestStatus' => $data['request_status'] ?? '',
			'createdAt' => $data['created_at'] ?? '',
		];
	}

	/**
	 * Get full table name.
	 *
	 * @return string
	 */
	private static function getFullTableName(): string
	{
		global $wpdb;
		return $wpdb->prefix . self::TABLE_NAME;
	}
}
