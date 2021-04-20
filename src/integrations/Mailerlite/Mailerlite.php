<?php

/**
 * Mailerlite integration class.
 *
 * @package EightshiftForms\Integrations
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailerlite;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Exception\MissingFilterInfoException;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftFormsVendor\MailerLiteApi\MailerLite as MailerLiteClient;

/**
 * Mailerlite integration class.
 */
class Mailerlite
{

  /**
   * Mailerlite Api client.
   *
   * @var MailerLiteClient
   */
	private $client;

  /**
   * Our own implementation of Mailerlite Client.
   *
   * @var ClientInterface
   */
	private $mailerliteClient;

  /**
   * Constructs object
   *
   * @param ClientInterface $mailerliteClient Mailerlite client.
   */
	public function __construct(ClientInterface $mailerliteClient)
	{
		$this->mailerliteClient = $mailerliteClient;
	}

  /**
   * Get all email groups
   *
   * @return mixed
   */
	public function getAllGroups()
	{
		$this->setupClientConfigAndVerify();

		return $this->client->groups()->get();
	}

  /**
   * Adds a subscriber in Mailerlite.
   *
   * @param  int    $groupId        Group ID.
   * @param  string $email           Contact's email.
   * @param  array  $subscriberData List of merge fields to add to request.
   * @param  array  $params          Additional params.
   * @return mixed
   *
   * @throws \Exception When response is invalid.
   */
	public function addSubscriber(int $groupId, string $email, array $subscriberData, array $params = [])
	{
		$this->setupClientConfigAndVerify();
		$subscriberData['email'] = $email;

		return $this->client->groups()->addSubscriber($groupId, $subscriberData, $params);
	}

  /**
   * Make sure we have the data we need defined as filters.
   *
   * @throws MissingFilterInfoException When not all required keys are set.
   *
   * @return void
   */
	private function setupClientConfigAndVerify(): void
	{
		if (! has_filter(Filters::MAILERLITE)) {
			throw MissingFilterInfoException::view_exception(Filters::MAILERLITE, esc_html__('entire_filter', 'eightshift-forms'));
		}

		if (empty(\apply_filters(Filters::MAILERLITE, 'api_key'))) {
			throw MissingFilterInfoException::view_exception(Filters::MAILERLITE, 'api_key');
		}

		if (empty($this->client)) {
			$this->mailerliteClient->setConfig();
			$this->client = $this->mailerliteClient->getClient();
		}
	}
}
