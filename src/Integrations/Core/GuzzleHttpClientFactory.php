<?php

/**
 * Guzzle client, implementation of HttpClient.
 *
 * @package EightshiftForms\Integrations\Core
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Core;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

/**
 * Guzzle client, implementation of HttpClientInterface.
 */
class GuzzleHttpClientFactory
{

	/**
	 * Creates the Guzzle http client.
	 *
	 * @return ClientInterface
	 */
	public static function create(): ClientInterface
	{
		return new Client();
	}
}
