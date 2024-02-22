<?php

/**
 * File containing Airtable specific interface.
 *
 * @package EightshiftForms\Integrations\Airtable
 */

namespace EightshiftForms\Integrations\Airtable;

use EightshiftForms\Integrations\ClientInterface;

/**
 * Interface for a Client
 */
interface AirtableClientInterface extends ClientInterface
{
	/**
	 * Return item details with cache option for faster loading.
	 *
	 * @param string $baseId Base ID to search by.
	 * @param string $itemId Item ID to search by.
	 *
	 * @return array<string, mixed>
	 */
	public function getItemDetails(string $baseId, string $itemId): array;
}
