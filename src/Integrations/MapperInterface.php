<?php

/**
 * The class for Mapper interface.
 *
 * @package EightshiftForms\Integrations
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations;

/**
 * Class Mapper
 */
interface MapperInterface
{
	/**
	 * Get mapped form fields.
	 *
	 * @param string $formId Form Id.
	 * @param bool $ssr Does form load using ssr.
	 *
	 * @return string<int, array<string, mixed>>
	 */
	public function getFormFields(string $formId, bool $ssr = false): array;

	/**
	 * Get Hubspot mapped form fields for block editor grammar.
	 *
	 * @param string $formId Form Id.
	 * @param string $itemId Integration item id.
	 *
	 * @return array
	 */
	public function getFormBlockGrammarArray(string $formId, string $itemId, string $innerId): array;
}
