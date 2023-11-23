<?php

/**
 * Entries class.
 *
 * @package EightshiftForms\Entries
 */

declare(strict_types=1);

namespace EightshiftForms\Entries;

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
	public function setEntryValue(array $values, string $formId): bool
	{
		global $wpdb;

		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$this->getFullTableName(),
			[
				'form_id' => (int) $formId,
				'entry_value' => $values,
			],
			[
				'%d',
				'%s',
			]
		);

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
