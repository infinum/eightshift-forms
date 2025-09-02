<?php

/**
 * Class that holds DB table creation - activity logs.
 *
 * @package EightshiftForms\Db
 */

declare(strict_types=1);

namespace EightshiftForms\Db;

use EightshiftForms\ActivityLog\ActivityLogHelper;

/**
 * CreateActivityLogsTable class.
 */
class CreateActivityLogsTable
{
	/**
	 * Create DB table.
	 *
	 * @return void
	 */
	public static function createTable(): void
	{
		require_once(\ABSPATH . 'wp-admin/includes/upgrade.php'); // @phpstan-ignore-line

		global $wpdb;

		$tableName = $wpdb->prefix . ActivityLogHelper::TABLE_NAME;

		$charsetCollate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$tableName} (
			id int(11) NOT NULL AUTO_INCREMENT,
			form_id int(11) NOT NULL,
			ip_address VARCHAR(255) NOT NULL,
			status_key VARCHAR(255) NOT NULL,
			data LONGTEXT NOT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id)
		) $charsetCollate;";

		\maybe_create_table($tableName, $sql);
	}
}
