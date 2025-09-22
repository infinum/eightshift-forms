<?php

/**
 * A model that represents a single rate limiting log entry.
 *
 * @package EightshiftForms\Security
 */

namespace EightshiftForms\Security;

use EightshiftForms\Db\CreateRateLimitingTable;
use RuntimeException;

/**
 * A model representing a single rate limiting log entry.
 */
class RateLimitingLogEntry
{
	// This class relies heavily on direct database calls that need to be uncached.
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

	// We require WP 6.2 for preparing the identifier name.
	// phpcs:disable WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder

	/**
	 * Construct a log entry model object.
	 *
	 * @param string $userToken A user-identifying token. Can be anything, but mostly a hashed IP address.
	 * @param string $activityType The type of activity that is being logged. E.g. submit-calculator.
	 * @param integer|null $logId A log ID, if used to represent an existing log entry, e.g. as a return value from ::find().
	 * @param integer|null $formId The ID of the form that the log entry is associated with.
	 * @param integer|null $createdAt The timestamp of the log entry creation.
	 */
	public function __construct(
		public readonly string $userToken,
		public readonly string $activityType,
		public readonly ?int $logId = null,
		public readonly ?int $formId = null,
		public ?int $createdAt = null,
	) {} // phpcs:ignore

	/**
	 * Write the log entry represented by the instance to the database as a new row.
	 *
	 * @throws RuntimeException If the form ID, user token or activity type is not set.
	 *
	 * @return RateLimitingLogEntry The log entry model object that was written to the database.
	 */
	public function write(): RateLimitingLogEntry
	{
		// Write the log entry to the database.
		$createdAt = $this->createdAt ?? \time();

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

		global $wpdb;
		$table = $wpdb->prefix . CreateRateLimitingTable::RATE_LIMITING_TABLE;

		$wpdb->query($wpdb->prepare("INSERT INTO %i (form_id, user_token, activity_type, created_at) VALUES (%d, %s, %s, %d)", $table, $this->formId, $this->userToken, $this->activityType, $this->createdAt));

		return new self(
			userToken: $this->userToken,
			activityType: $this->activityType,
			logId: $wpdb->insert_id, // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
			formId: $this->formId,
			createdAt: $createdAt,
		);
	}

	/**
	 * Find matching log entries in the database, given a user token, activity type and a time window.
	 *
	 * @param string $userToken The user token to seek.
	 * @param string $activityType The activity type to seek.
	 * @param integer $windowDuration The time window (from current time) in seconds.
	 *
	 * @return array<RateLimitingLogEntry> An array of log entries that match the criteria, as RateLimitingLogEntry objects.
	 */
	public static function find(
		string $userToken,
		string $activityType,
		int $windowDuration
	): array {
		$windowStart = \time() - $windowDuration;

		global $wpdb;
		$table = $wpdb->prefix . CreateRateLimitingTable::RATE_LIMITING_TABLE;

		$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM %i WHERE user_token = %s AND activity_type = %s AND created_at >= %d", $table, $userToken, $activityType, $windowStart));

		return \array_map(static function ($result) {
			// We use snake-case in the database column names.
			// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
			return new RateLimitingLogEntry(
				userToken: $result->user_token,
				activityType: $result->activity_type,
				logId: $result->log_id,
				formId: $result->form_id,
				createdAt: $result->created_at,
			);
			// phpcs:enable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
		}, $results);
	}

	/**
	 * Count the number of log entries in the database, given a user token, form ID and a time window.
	 *
	 * @param string $userToken The user token to seek.
	 * @param integer $formId The form ID to seek.
	 * @param integer $windowDuration The time window (from current time) in seconds.
	 *
	 * @return integer The number of log entries that match the criteria.
	 */
	public static function countByFormId(
		string $userToken,
		int $formId,
		int $windowDuration
	): int {
		$windowStart = \time() - $windowDuration;

		global $wpdb;
		$table = $wpdb->prefix . CreateRateLimitingTable::RATE_LIMITING_TABLE;

		return (int)($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM %i WHERE user_token = %s AND form_id = %d AND created_at >= %d", $table, $userToken, $formId, $windowStart)));
	}

	/**
	 * Find aggregated log entries in the database, given a user token and a time window.
	 *
	 * @param string $userToken The user token to seek.
	 * @param integer $windowDuration The time window (from current time) in seconds.
	 *
	 * @return array<array<string, mixed>> An array of aggregated log entries that match the criteria. Keys are `activityType` and `count`.
	 */
	public static function findAggregatedByActivityType(
		string $userToken,
		int $windowDuration
	): array {
		$windowStart = \time() - $windowDuration;

		global $wpdb;
		$table = $wpdb->prefix . CreateRateLimitingTable::RATE_LIMITING_TABLE;

		$results = $wpdb->get_results($wpdb->prepare("SELECT activity_type, COUNT(*) as count FROM %i WHERE user_token = %s AND created_at >= %d GROUP BY activity_type", $table, $userToken, $windowStart));

		// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
		return \array_map(static function ($result) {
			return [
				'activityType' => $result->activity_type,
				'count' => $result->count,
			];
		// phpcs:enable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
		}, $results);
	}

	/**
	 * Cleanup old log entries from the database.
	 *
	 * @param integer $windowDuration The time window (from current time) in seconds to keep log entries.
	 *
	 * @return void
	 */
	public static function cleanup(int $windowDuration): void
	{
		$windowStart = \time() - $windowDuration;

		global $wpdb;
		$table = $wpdb->prefix . CreateRateLimitingTable::RATE_LIMITING_TABLE;

		$wpdb->query($wpdb->prepare("DELETE FROM %i WHERE created_at < %d", $table, $windowStart));
	}

	// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	// phpcs:enable WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder
}
