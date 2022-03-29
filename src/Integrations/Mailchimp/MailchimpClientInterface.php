<?php

/**
 * File containing Mailchimp specific interface.
 *
 * @package EightshiftForms\Integrations\Mailchimp
 */

namespace EightshiftForms\Integrations\Mailchimp;

use EightshiftForms\Integrations\ClientInterface;

/**
 * Interface for a Client
 */
interface MailchimpClientInterface extends ClientInterface
{
	/**
	 * Return Mailchimp tags for a list.
	 *
	 * @param string $itemId Item id to search.
	 *
	 * @return array<int, mixed>
	 */
	public function getTags(string $itemId): array;
}
