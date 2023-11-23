<?php

/**
 * Entries class.
 *
 * @package EightshiftForms\Entries
 */

declare(strict_types=1);

namespace EightshiftForms\Entries;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Integrations\Mailer\SettingsMailer;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;

/**
 * Entries class.
 */
class Entries implements EntriesInterface
{
	/**
	 * Job name.
	 *
	 * @var string
	 */
	public const TABLE_NAME = 'es_forms_entries';

	/**
	 * Get entry value.
	 *
	 * @param array<string, mixed> $values Values to store.
	 * @param string $formId Form Id.
	 *
	 * @return boolean
	 */
	public function setEntryValue(array $formDataReference, string $formId): bool
	{
		global $wpdb;
		
		$type = $formDataReference['type'] ?? '';
		$params = $formDataReference['params'] ?? [];

		$output = [];

		switch ($type) {
			case SettingsMailer::SETTINGS_TYPE_KEY:
				$params = Helper::removeUneceseryParamFields($params);

				foreach ($params as $param) {
					$name = $param['name'] ?? '';
					$value = $param['value'] ?? '';

					if (!$name || !$value) {
						continue;
					}

					if (gettype($value) === 'array') {
						$value = implode(AbstractBaseRoute::DELIMITER, $value);
					}

					$output[$name] = $value;
				}
				break;
			
			default:
				break;
		}

		if (!$output) {
			return false;

		}

		error_log( print_r( ( $output ), true ) );
		

		// $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		// 	$this->getFullTableName(),
		// 	[
		// 		'form_id' => (int) $formId,
		// 		'entry_value' => $values,
		// 	],
		// 	[
		// 		'%d',
		// 		'%s',
		// 	]
		// );

		return true;
	}

	/**
	 * Get full table name.
	 *
	 * @return string
	 */
	private function getFullTableName(): string
	{
		global $wpdb;
		return $wpdb->prefix . self::TABLE_NAME;
	}
}
