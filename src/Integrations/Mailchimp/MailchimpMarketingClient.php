<?php

/**
 * Mailchimp marketing client implementation
 *
 * @package EightshiftForms\Integrations\Mailchimp
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailchimp;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\ClientInterface;
use MailchimpMarketing\ApiClient;

/**
 * Mailchimp integration class.
 */
class MailchimpMarketingClient implements ClientInterface
{

	/**
	 * Mailchimp API's Marketing client object.
	 *
	 * @var ApiClient
	 */
	private $client;

	/**
	 * Constructs object
	 */
	public function __construct()
	{
		$this->client = new ApiClient();
	}

	/**
	 * Sets the config because we can't set config during construction (filters aren't yet registered)
	 *
	 * @return void
	 */
	public function setConfig()
	{
		$this->client->setConfig([
			'apiKey' => \has_filter(Filters::MAILCHIMP) ? \apply_filters(Filters::MAILCHIMP, 'apiKey') : '',
			'server' => \has_filter(Filters::MAILCHIMP) ? \apply_filters(Filters::MAILCHIMP, 'server') : '',
		]);
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
