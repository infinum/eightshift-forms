<?php

/**
 * File containing ActiveCampaign specific interface.
 *
 * @package EightshiftForms\Integrations\ActiveCampaign
 */

namespace EightshiftForms\Integrations\ActiveCampaign;

use EightshiftForms\Integrations\ClientInterface;

/**
 * Interface for a Client
 */
interface ActiveCampaignClientInterface extends ClientInterface
{
	/**
	 * API request to post tags.
	 *
	 * @param string $tag Tag to store.
	 * @param string $contactId Contact ID to store.
	 *
	 * @return array<string, mixed>
	 */
	public function postTag(string $tag, string $contactId): array;

	/**
	 * API request to post list.
	 *
	 * @param string $list List to store.
	 * @param string $contactId Contact ID to store.
	 *
	 * @return array<string, mixed>
	 */
	public function postList(string $list, string $contactId): array;
}
