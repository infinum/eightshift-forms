<?php

/**
 * HttpClient interface.
 *
 * @package EightshiftForms\Integrations\Core
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Core;

/**
 * HttpClient interface.
 */
interface HttpClientInterface
{

	/**
	 * Implementation of get request on the HttpClient.
	 *
	 * @param  string $url        Url to ping.
	 * @param  array  $parameters (Optional) parameters for the request.
	 * @return mixed
	 */
	public function get(string $url, array $parameters = []);

	/**
	 * Implementation of post request on the HttpClient.
	 *
	 * @param  string $url        Url to ping.
	 * @param  array  $parameters (Optional) parameters for the request.
	 * @return mixed
	 */
	public function post(string $url, array $parameters = []);
}
