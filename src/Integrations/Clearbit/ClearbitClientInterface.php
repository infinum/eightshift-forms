<?php

/**
 * File containing Clearbit specific interface.
 *
 * @package EightshiftForms\Integrations\Clearbit
 */

namespace EightshiftForms\Integrations\Clearbit;

/**
 * Interface for a Client
 */
interface ClearbitClientInterface
{
	/**
	 * Get mapped params.
	 *
	 * @return array<int, string>
	 */
	public function getParams(): array;

	/**
	 * API request to post application.
	 *
	 * @param string $email Email key to map in params.
	 * @param array<string, mixed> $params Params array.
	 * @param array<string, string> $mapData Map data from settings.
	 * @param string $itemId Item id to search.
	 * @param string $formId FormId value.
	 *
	 * @return array<string, mixed>
	 */
	public function getApplication(string $email, array $params, array $mapData, string $itemId, string $formId): array;
}
