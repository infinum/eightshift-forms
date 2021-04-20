<?php

/**
 * Mailerlite client implementation
 *
 * @package EightshiftForms\Integrations\Mailerlite
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailerlite;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\ClientInterface;
use MailerLiteApi\MailerLite;
use GuzzleHttp\Client as GuzzleHttp;
use Http\Adapter\Guzzle6\Client as Guzzle6;

/**
 * Mailerlite integration class.
 */
class MailerliteClient implements ClientInterface
{

  /**
   * Mailerlite client object.
   *
   * @var MailerLite
   */
	private $client;

  /**
   * Sets the config because we can't set config during construction (filters aren't yet registered)
   *
   * @return void
   */
	public function setConfig()
	{
		$apiKey = \has_filter(Filters::MAILERLITE) ? \apply_filters(Filters::MAILERLITE, 'api_key') : '';

		$guzzle = new GuzzleHttp();
		$guzzleClient = new Guzzle6($guzzle);

		$this->client = new MailerLite($apiKey, $guzzleClient);
	}

  /**
   * Returns the build client
   *
   * @return mixed
   */
	public function getClient()
	{
		return $this->client;
	}
}
