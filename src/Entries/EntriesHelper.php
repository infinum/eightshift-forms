<?php

/**
 * EntriesHelper class.
 *
 * @package EightshiftForms\Entries
 */

declare(strict_types=1);

namespace EightshiftForms\Entries;

use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\HooksHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Helpers\SettingsHelpers;

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
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return int|false
	 */
	public static function setEntryByFormDataRef(array $formDetails)
	{
		$params = \array_merge(
			$formDetails[Config::FD_PARAMS] ?? [],
			$formDetails[Config::FD_FILES] ?? []
		);
		$formId = $formDetails[Config::IARD_FORM_ID] ?? '';

		$output = [];

		// Filter params.
		$filterName = HooksHelpers::getFilterName(['entries', 'prePostParams']);
		if (\has_filter($filterName)) {
			$params = \apply_filters($filterName, $params, $formId, $formDetails) ?? [];
		}

		// Get skipped params earlier as we are removing them from the main array using removeUnnecessaryParamFields method.
		$paramsSkipped = FormsHelper::getParamValue(UtilsHelper::getStateParam('skippedParams'), $params);

		$params = GeneralHelpers::removeUnnecessaryParamFields($params);

		$saveEmptyFields = SettingsHelpers::isSettingCheckboxChecked(SettingsEntries::SETTINGS_ENTRIES_SAVE_EMPTY_FIELDS, SettingsEntries::SETTINGS_ENTRIES_SAVE_EMPTY_FIELDS, $formId);

		foreach ($params as $param) {
			$name = $param['name'] ?? '';
			$value = $param['value'] ?? '';
			$type = $param['type'] ?? '';

			if (!$name || !$type) {
				continue;
			}

			if (!$value && !$saveEmptyFields) {
				continue;
			}

			if ($type === 'file') {
				$value = \array_map(
					static function (string $file) {
						$filename = \pathinfo($file, \PATHINFO_FILENAME);
						$extension = \pathinfo($file, \PATHINFO_EXTENSION);
						return "{$filename}.{$extension}";
					},
					$value
				);
			}

			$output[$name] = $value;
		}

		// Output skipped params as empty strings.
		if ($paramsSkipped && $saveEmptyFields) {
			foreach ($paramsSkipped as $key => $value) {
				$output[$key] = '';
			}
		}

		if (!$output) {
			return false;
		}

		return self::setEntry($output, $formId);
	}

	/**
	 * Get entry admin URL.
	 *
	 * @param string $entryId Entry Id.
	 * @param string $formId Form Id.
	 *
	 * @return string
	 */
	public static function getEntryAdminUrl(string $entryId, string $formId): string
	{
		return GeneralHelpers::getListingPageUrl(Config::SLUG_ADMIN_LISTING_ENTRIES, $formId) . "#entry-{$entryId}";
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
	 * Get all entries.
	 *
	 * @param int $page Page number.
	 * @param int $perPage Number of items per page.
	 * @param string $search Search query.
	 *
	 * @return array<string, mixed>
	 */
	public static function getEntriesAll(int $page = 1, int $perPage = Config::PER_PAGE_DEFAULT, string $search = ''): array
	{
		global $wpdb;

		$tableName = self::getFullTableName();

		$output = \wp_cache_get('all', self::TABLE_NAME . 'entry');

		if (!$output) {
			$output = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->prepare(
					"SELECT * FROM {$tableName} WHERE entry_value LIKE %s ORDER BY created_at DESC LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					[
						'%' . $search . '%',
						(int) $perPage,
						(int) $perPage * ($page - 1),
					]
				),
				\ARRAY_A
			);

			\wp_cache_add('all', $output, self::TABLE_NAME . 'entry');
		}

		if (\is_wp_error($output) || !$output) {
			return [];
		}

		$results = [];

		foreach ($output as $key => $value) {
			$results[$key] = self::prepareEntryOutput($value);
		}

		$totalPages = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$tableName} WHERE entry_value LIKE %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				[
					'%' . $search . '%',
				]
			)
		);

		return [
			'currentPage' => (int) $page,
			'totalPages' => (int) \ceil($totalPages / $perPage),
			'count' => \count($results),
			'items' => $results,
		];
	}

	/**
	 * Get entries by form ID.
	 *
	 * @param string $formId Form Id.
	 * @param int $page Page number.
	 * @param int $perPage Number of items per page.
	 * @param string $search Search query.
	 *
	 * @return array<mixed>
	 */
	public static function getEntries(string $formId, int $page = 1, int $perPage = Config::PER_PAGE_DEFAULT, string $search = ''): array
	{
		global $wpdb;

		$tableName = self::getFullTableName();

		$output = \wp_cache_get($formId, self::TABLE_NAME . 'entries');

		if (!$output) {
			$output = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->prepare(
					"SELECT * FROM {$tableName} WHERE form_id = %d AND (entry_value LIKE %s) ORDER BY created_at DESC LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					[
						(int) $formId,
						'%' . $search . '%',
						(int) $perPage,
						(int) $perPage * ($page - 1),
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

		$totalPages = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$tableName} WHERE form_id = %d AND (entry_value LIKE %s)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				[
					(int) $formId,
					'%' . $search . '%',
				]
			)
		);

		return [
			'currentPage' => (int) $page,
			'totalPages' => (int) \ceil($totalPages / $perPage),
			'count' => \count($results),
			'items' => $results,
		];
	}

	/**
	 * Set entry.
	 *
	 * @param array<string, mixed> $data Data to save.
	 * @param string $formId Form Id.
	 *
	 * @return int|false
	 */
	public static function setEntry(array $data, string $formId)
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

		return $wpdb->insert_id; //phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
	}

	/**
	 * Update entry.
	 *
	 * @param array<string, mixed> $data Data to update.
	 * @param string $id Entry Id.
	 *
	 * @return boolean
	 */
	public static function updateEntry(array $data, string $id): bool
	{
		global $wpdb;

		$output = \wp_json_encode($data);

		$result = $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			self::getFullTableName(),
			[
				'entry_value' => $output,
			],
			[
				'id' => (int) $id,
			],
			[
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
