<?php

/**
 * Mailerlite client implementation
 *
 * @package EightshiftForms\Integrations\Mailchimp
 */

declare( strict_types=1 );

namespace EightshiftFormsTests\Mocks;

use EightshiftForms\Integrations\ClientInterface;
use Codeception\Stub;
use EightshiftFormsTests\Integrations\Mailerlite\DataProvider;
use EightshiftFormsVendor\MailerLiteApi\Api\Groups;
use EightshiftFormsVendor\MailerLiteApi\MailerLite;

/**
 * Mailerlite integration class.
 */
class MockMailerliteClient implements ClientInterface {

  /**
   * Constructs object
   */
  public function __construct() {
    $this->client = Stub::make(MailerLite::class, [
      'groups' => Stub::make(Groups::class, [
        'addSubscriber' => function ($groupId, $subscriberData = [], $params = []) {
          return DataProvider::getMockAddSubscriberResponse($params);
        },
      ]),
    ]);
  }

  /**
   * Mock setting config.
   *
   * @return object
   */
  public function setConfig() {
    // do nothing.
  }

  /**
   * Returns the build client
   *
   * @return object
   */
  public function getClient() {
    return $this->client;
  }
}
