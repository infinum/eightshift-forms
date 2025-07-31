<?php

/**
 * ActivityLogHelper class.
 *
 * @package EightshiftForms\ActivityLog
 */

declare(strict_types=1);

namespace EightshiftForms\ActivityLog;

use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\GeneralHelpers;

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
		return GeneralHelpers::getListingPageUrl(Config::SLUG_ADMIN_LISTING_ACTIVITY_LOGS, $formId) . "#activity-log-{$activityLogId}";
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
	 * @param string $ip IP address of the request.
	 * @param string $statusKey Status key of the activity log.
	 * @param string $formId Form ID.
	 * @param array<string, mixed> $data Data to save.
	 *
	 * @return int|false
	 */
	public static function setActivityLog(
		string $ip,
		string $statusKey,
		string $formId,
		array $data
	) {
		global $wpdb;

		if (!$formId) {
			return false;
		}

		$result = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			self::getFullTableName(),
			[
				'ip_address' => $ip,
				'status_key' => $statusKey,
				'form_id' => (int) $formId,
				'data' => \wp_json_encode($data),
				'created_at' => \current_time('mysql', true),
			],
			[
				'%s',
				'%s',
				'%d',
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
			'statusKey' => $data['status_key'] ?? '',
			'ipAddress' => $data['ip_address'] ?? '',
			'formId' => $data['form_id'] ?? '',
			'data' => $data['data'] ?? '',
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
