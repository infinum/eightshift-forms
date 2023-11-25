<?php

/**
 * Class that holds DB table creation - entries.
 *
 * @package EightshiftForms\Db
 */

declare(strict_types=1);

namespace EightshiftForms\Db;

use EightshiftForms\Entries\EntriesHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * CreateEntriesTable class.
 */
class CreateEntriesTable implements ServiceInterface
{
	/**
	 * Register all the hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('init', [$this, 'createTable']);
	}

	/**
	 * Create DB table.
	 *
	 * @return void
	 */
	public function createTable(): void
	{
		global $wpdb;
		$tableName = $wpdb->prefix . EntriesHelper::TABLE_NAME;

		$charsetCollate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$tableName} (
			id int(11) NOT NULL AUTO_INCREMENT,
			form_id int(11) NOT NULL,
			entry_value LONGTEXT NOT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id)
		) $charsetCollate;";

		require_once(\ABSPATH . 'wp-admin/includes/upgrade.php');
		\dbDelta($sql);
	}
}
