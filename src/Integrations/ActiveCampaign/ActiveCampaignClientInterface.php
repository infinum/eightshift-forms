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
	 * Return ActiveCampaign tags for a list.
	 *
	 * @param string $itemId Item id to search.
	 *
	 * @return array<int, mixed>
	 */
	public function getTags(string $itemId): array;
}
