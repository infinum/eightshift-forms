<?php

/**
 * Class that holds DB table creation - entries.
 *
 * @package EightshiftForms\Db
 */

declare(strict_types=1);

namespace EightshiftForms\Db;

use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * CreateEntriesTable class.
 */
class CreateEntriesTable implements ServiceInterface
{
	/**
	 * Job name.
	 *
	 * @var string
	 */
	public const TABLE_NAME = 'es_forms_entries';

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
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id int(11) NOT NULL AUTO_INCREMENT,
			form_id int(11) NOT NULL,
			entry_value LONGTEXT NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
	}
}

