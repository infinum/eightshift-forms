<?php

/**
 * Class that holds DB sync functionality.
 *
 * @package EightshiftForms\Db
 */

declare(strict_types=1);

namespace EightshiftForms\Db;

use EightshiftForms\Config\Config;
use EightshiftForms\ActivityLog\ActivityLogHelper;
use EightshiftForms\Entries\EntriesHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * DbSync class.
 */
class DbSync implements ServiceInterface
{
	/**
	 * Sync DB.
	 *
	 * @var string
	 */
	public const string OPTION_NAME = 'es_forms_db_version';

	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('admin_init', [$this, 'syncDb']);
	}

	/**
	 * Sync DB.
	 *
	 * @return void
	 */
	public function syncDb(): void
	{
		$currentVersion = (int) \get_option(self::OPTION_NAME);
		$targetVersion = Config::DB_CURRENT_VERSION;

		// If version matches, no sync needed.
		if ($currentVersion === $targetVersion) {
			return;
		}

		// Install missing tables and update version.
		$this->installTables();
		\update_option(self::OPTION_NAME, $targetVersion);
	}

	/**
	 * Install database tables. Only installs tables that don't already exist.
	 *
	 * @return void
	 */
	private function installTables(): void
	{
		global $wpdb;

		$entriesTable = $wpdb->prefix . EntriesHelper::TABLE_NAME;
		$activityLogTable = $wpdb->prefix . ActivityLogHelper::TABLE_NAME;
		$rateLimitingTable = $wpdb->prefix . CreateRateLimitingTable::RATE_LIMITING_TABLE;

		if (!$this->tableExists($entriesTable)) {
			CreateEntriesTable::createTable();
		}

		if (!$this->tableExists($activityLogTable)) {
			CreateActivityLogsTable::createTable();
		}

		if (!$this->tableExists($rateLimitingTable)) {
			CreateRateLimitingTable::createTable();
		}
	}

	/**
	 * Check if a database table exists.
	 *
	 * @param string $tableName Full table name with prefix.
	 * @return bool
	 */
	private function tableExists(string $tableName): bool
	{
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		global $wpdb;

		// Escape table name for safe use in query.
		$tableName = \esc_sql($tableName);

		// Use information_schema to check if table exists.
		$dbName = \DB_NAME; // @phpstan-ignore-line
		$result = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s',
				$dbName,
				$tableName
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		return (int) $result > 0;
	}
}
