<?php

/**
 * EntriesHelper class.
 *
 * @package EightshiftForms\Entries
 */

declare(strict_types=1);

namespace EightshiftForms\Entries;

use EightshiftForms\Helpers\Helper;

/**
 * EntriesHelper class.
 */
class EntriesHelper
{
	/**
	 * Table name.
	 *
	 * @var string
	 */
	public const TABLE_NAME = 'es_forms_entries';

	/**
	 * Get entry by form data reference.
	 *
	 * @param array<string, mixed> $formDataReference Form data reference.
	 * @param string $formId Form Id.
	 *
	 * @return boolean
	 */
	public static function setEntryByFormDataRef(array $formDataReference, string $formId): bool
	{
		$params = $formDataReference['params'] ?? [];

		$output = [];

		$params = Helper::removeUneceseryParamFields($params);

		foreach ($params as $param) {
			$name = $param['name'] ?? '';
			$value = $param['value'] ?? '';

			if (!$name || !$value) {
				continue;
			}

			$output[$name] = $value;
		}

		if (!$output) {
			return false;
		}

		return self::setEntry($output, $formId);
	}

	/**
	 * Get entry by ID.
	 *
	 * @param string $id Entry Id.
	 *
	 * @return array<string, mixed>
	 */
	public static function getEntry(string $id): array
	{
		global $wpdb;

		$tableName = self::getFullTableName();

		$output = \wp_cache_get($id, self::TABLE_NAME . 'entry');

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

			\wp_cache_add($id, $output, self::TABLE_NAME . 'entry');
		}

		if (\is_wp_error($output) || !$output) {
			return [];
		}

		return self::prepareEntryOutput($output);
	}

	/**
	 * Get entries by form ID.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<mixed>
	 */
	public static function getEntries(string $formId): array
	{
		global $wpdb;

		$tableName = self::getFullTableName();

		$output = \wp_cache_get($formId, self::TABLE_NAME . 'entries');

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

			\wp_cache_add($formId, $output, self::TABLE_NAME . 'entries');
		}

		if (\is_wp_error($output) || !$output) {
			return [];
		}

		$results = [];

		foreach ($output as $value) {
			$results[] = self::prepareEntryOutput($value);
		}

		return $results;
	}

	/**
	 * Get entries count by form ID.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return string
	 */
	public static function getEntriesCount(string $formId): string
	{
		global $wpdb;

		$tableName = self::getFullTableName();

		$output = \wp_cache_get($formId, self::TABLE_NAME . 'entries_count');

		if (!$output) {
			$output = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$tableName} WHERE form_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					[
						(int) $formId
					]
				)
			) ?: '0'; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

			\wp_cache_add($formId, $output, self::TABLE_NAME . 'entries_count');
		}


		return $output;
	}

	/**
	 * Set entry.
	 *
	 * @param array<string, mixed> $data Data to save.
	 * @param string $formId Form Id.
	 *
	 * @return boolean
	 */
	public static function setEntry(array $data, string $formId): bool
	{
		global $wpdb;

		$output = \wp_json_encode($data);

		$time = \current_time('mysql', true);

		$result = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			self::getFullTableName(),
			[
				'form_id' => (int) $formId,
				'entry_value' => $output,
				'created_at' => $time,
			],
			[
				'%d',
				'%s',
				'%s',
			]
		);

		if (\is_wp_error($result)) {
			return false;
		}

		return true;
	}

	/**
	 * Delete entry.
	 *
	 * @param string $id Entry Id.
	 *
	 * @return boolean
	 */
	public static function deleteEntry(string $id): bool
	{
		global $wpdb;

		$tableName = self::getFullTableName();

		$output = $wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$tableName,
			[
				'id' => (int) $id,
			],
			[
				'%d',
			]
		);

		if (\is_wp_error($output)) {
			return false;
		}

		if (\wp_cache_get($id, self::TABLE_NAME . 'entry')) {
			\wp_cache_delete($id, self::TABLE_NAME . 'entry');
		}

		return true;
	}

	/**
	 * Prepare entry output from DB.
	 *
	 * @param array<string, mixed> $data Data to prepare.
	 *
	 * @return array<string, mixed>
	 */
	private static function prepareEntryOutput(array $data): array
	{
		return [
			'id' => $data['id'] ?? '',
			'formId' => $data['form_id'] ?? '',
			'entryValue' => isset($data['entry_value']) ? \json_decode($data['entry_value'], true) : [],
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
