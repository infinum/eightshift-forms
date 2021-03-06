<?php

/**
 * Mailerlite client implementation
 *
 * @package EightshiftForms\Integrations\Mailchimp
 */

declare(strict_types=1);

namespace EightshiftFormsTests\Mocks;

use Codeception\Stub;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftFormsTests\Integrations\Mailerlite\DataProvider;
use MailerLiteApi\Api\Groups;
use MailerLiteApi\MailerLite;

/**
 * Mailerlite integration class.
 */
class MockMailerliteClient implements ClientInterface
{

	/**
	 * Constructs object
	 */
	public function __construct()
	{
		$this->client = Stub::make(
			MailerLite::class,
			[
				'groups' => Stub::make(
					Groups::class,
					[
						'addSubscriber' => function ($groupId, $subscriberData = [], $params = []) {
							return DataProvider::getMockAddSubscriberResponse($params);
						},
					]
				),
			]
		);
	}

	/**
	 * Mock setting config.
	 *
	 * @return object
	 */
	public function setConfig()
	{
		// do nothing.
	}

	/**
	 * Returns the build client
	 *
	 * @return object
	 */
	public function getClient()
	{
		return $this->client;
	}
}
