<?php

/**
 * A model that represents a single rate limiting log entry.
 *
 * @package EightshiftForms\Security
 */

namespace EightshiftForms\Security;

use RuntimeException;

/**
 * A model representing a single rate limiting log entry.
 */
class RateLimitingLogEntry
{
	public function __construct(
		public readonly ?int $logId = null,
		public readonly ?int $formId = null,
		public readonly string $userToken,
		public readonly string $activityType,
		public readonly ?int $createdAt = null,
	) {}

	public function write()
	{
		// Write the log entry to the database.
		if (!$this->createdAt) {
			$this->createdAt = time();
		}

		if (!$this->formId) {
			throw new RuntimeException("Form ID is required to write a rate limiting log entry.");
		}

		if (!$this->userToken) {
			throw new RuntimeException("User token is required to write a rate limiting log entry.");
		}

		if (!$this->activityType) {
			throw new RuntimeException("Activity type is required to write a rate limiting log entry.");
		}

		if ($this->logId) {
			throw new RuntimeException("Log ID must be null to write a new rate limiting log entry.");
		}

		$tableName = Security::RATE_LIMITING_TABLE;

		global $wpdb;
		$query = "INSERT INTO {$wpdb->prefix}{$tableName} (form_id, user_token, activity_type, created_at) VALUES (%d, %s, %s, %d)";
		$wpdb->query($wpdb->prepare($query, $this->formId, $this->userToken, $this->activityType, $this->createdAt));
	}

	public static function find(
		string $userToken,
		string $activityType,
		int $windowDuration
	): array {
		$windowStart = time() - $windowDuration;

		$tableName = Security::RATE_LIMITING_TABLE;

		global $wpdb;
		$query = "SELECT * FROM {$wpdb->prefix}{$tableName} WHERE user_token = %s AND activity_type = %s AND created_at >= %d";
		$results = $wpdb->get_results($wpdb->prepare($query, $userToken, $activityType, $windowStart));
		return array_map(function ($result) {
			return new RateLimitingLogEntry(
				logId: $result->log_id,
				formId: $result->form_id,
				userToken: $result->user_token,
				activityType: $result->activity_type,
				createdAt: $result->created_at,
			);
		}, $results);
	}

	public static function countByFormId(
		string $userToken,
		int $formId,
		int $windowDuration
	): int {
		$windowStart = time() - $windowDuration;
		$tableName = Security::RATE_LIMITING_TABLE;

		global $wpdb;
		$query = "SELECT COUNT(*) FROM {$wpdb->prefix}{$tableName} WHERE user_token = %s AND form_id = %d AND created_at >= %d";
		return (int)($wpdb->get_var($wpdb->prepare($query, $userToken, $formId, $windowStart)));
	}

	public static function findAggregatedByActivityType(
		string $userToken,
		int $windowDuration
	): array {
		$windowStart = time() - $windowDuration;
		$tableName = Security::RATE_LIMITING_TABLE;

		global $wpdb;
		$query = "SELECT activity_type, COUNT(*) as count FROM {$wpdb->prefix}{$tableName} WHERE user_token = %s AND created_at >= %d GROUP BY activity_type";
		$results = $wpdb->get_results($wpdb->prepare($query, $userToken, $windowStart));
		return array_map(function ($result) {
			return [
				'activityType' => $result->activity_type,
				'count' => $result->count,
			];
		}, $results);
	}

	public static function cleanup(int $windowDuration)
	{
		$windowStart = time() - $windowDuration;
		$tableName = Security::RATE_LIMITING_TABLE;

		global $wpdb;
		$query = "DELETE FROM {$wpdb->prefix}{$tableName} WHERE created_at < %d";
		$wpdb->query($wpdb->prepare($query, $windowStart));
	}
}
