<?php

/**
 * Interface that holds all methods for getting forms entries usage.
 *
 * @package EightshiftForms\Entries
 */

declare(strict_types=1);

namespace EightshiftForms\Entries;

/**
 * Interface for EntriesInterface
 */
interface EntriesInterface
{

	/**
	 * Get entry value.
	 *
	 * @param array<string, mixed> $values Values to store.
	 * @param string $formId Form Id.
	 *
	 * @return boolean
	 */
	public function setEntryValue(array $values, string $formId): bool;
}
