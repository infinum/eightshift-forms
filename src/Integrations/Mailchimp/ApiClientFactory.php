<?php

/**
 * Factory for Mailchimp client.
 *
 * @package EightshiftForms\Integrations\Mailchimp
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailchimp;

use MailchimpMarketing\ApiClient;

/**
 * Factory for Mailchimp client.
 */
class ApiClientFactory
{

	/**
	 * Sets the config because we can't set config during construction (filters aren't yet registered)
	 *
	 * @return ApiClient
	 */
	public static function build(): ApiClient
	{
		return new ApiClient();
	}
}
