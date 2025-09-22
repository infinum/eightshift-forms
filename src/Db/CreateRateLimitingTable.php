<?php

/**
 * Class that holds DB table creation for rate limiting.
 *
 * @package EightshiftForms\Db
 */

declare(strict_types=1);

namespace EightshiftForms\Db;

/**
 * CreateRateLimitingTable class.
 */
class CreateRateLimitingTable
{
	/**
	 * Table name
	 *
	 * @var string
	 */
	public const string RATE_LIMITING_TABLE = 'es_forms_rate_limiting';

	/**
	 * Create DB table.
	 *
	 * @return void
	 */
	public static function createTable(): void
	{
		require_once(\ABSPATH . 'wp-admin/includes/upgrade.php');  // @phpstan-ignore-line

		global $wpdb;

		$tableName = $wpdb->prefix . self::RATE_LIMITING_TABLE;

		$charsetCollate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$tableName} (
			`log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`form_id` int(11) DEFAULT NULL,
			`user_token` varchar(256) NOT NULL,
			`activity_type` varchar(256) NOT NULL,
			`created_at` bigint(20) NOT NULL,
			PRIMARY KEY (`log_id`),
			KEY `token_time` (`user_token`,`created_at`),
			KEY `token_form_time` (`user_token`,`form_id`,`created_at`),
			KEY `token_activity_time` (`user_token`,`activity_type`,`created_at`),
			KEY `token_form_activity_time` (`user_token`,`form_id`,`activity_type`,`created_at`)
		) $charsetCollate;";

		\maybe_create_table($tableName, $sql);
	}
}
