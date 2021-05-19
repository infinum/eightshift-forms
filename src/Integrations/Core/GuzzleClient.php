<?php

/**
 * Guzzle client, implementation of HttpClient.
 *
 * @package EightshiftForms\Integrations\Core
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Core;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;

/**
 * Guzzle client, implementation of HttpClientInterface.
 */
class GuzzleClient implements HttpClientInterface
{

	/**
	 * Guzzle client object usd for making requests.
	 *
	 * @var ClientInterface.
	 */
	private $client;

	/**
	 * Constructs object.
	 */
	public function __construct()
	{
		$this->client = GuzzleHttpClientFactory::create();
	}

	/**
	 * Implementation of get request on the HttpClient.
	 *
	 * @param  string $url        Url to ping.
	 * @param  array  $parameters (Optional) parameters for the request.
	 * @return mixed
	 */
	public function get(string $url, array $parameters = [])
	{
		return $this->client->request('GET', $url, $parameters);
	}

	/**
	 * Implementation of post request on the HttpClient.
	 *
	 * @param  string $url        Url to ping.
	 * @param  array  $parameters (Optional) parameters for the request.
	 * @return mixed
	 */
	public function post(string $url, array $parameters = [])
	{
		return $this->client->request('POST', $url, $parameters);
	}

	/**
	 * Implementation of post request on the HttpClient.
	 *
	 * @param  string $url        Url to ping.
	 * @param  array  $parameters (Optional) parameters for the request.
	 * @return mixed
	 */
	public function patch(string $url, array $parameters = [])
	{
		return $this->client->request('PATCH', $url, $parameters);
	}
}
