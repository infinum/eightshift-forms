<?php

/**
 * File containing Hubspot specific interface.
 *
 * @package EightshiftForms\Integrations\Hubspot
 */

namespace EightshiftForms\Integrations\Hubspot;

use EightshiftForms\Integrations\ClientInterface;

/**
 * Interface for a Client
 */
interface HubspotClientInterface extends ClientInterface
{
	/**
	 * Return contact properties with cache option for faster loading.
	 *
	 * @return array<string, mixed>
	 */
	public function getContactProperties(): array;

	/**
	 * Post contact property to HubSpot.
	 *
	 * @param string $email Email to connect data to.
	 * @param array<string, mixed> $params Params array.
	 *
	 * @return array<string, mixed>
	 */
	public function postContactProperty(string $email, array $params): array;
}
