<?php

/**
 * Mailerlite client implementation
 *
 * @package Eightshift_Forms\Integrations\Mailchimp
 */

declare( strict_types=1 );

namespace EightshiftFormsTests\Mocks;

use Eightshift_Forms\Integrations\Client_Interface;
use Codeception\Stub;
use EightshiftFormsTests\Integrations\Mailerlite\DataProvider;
use \MailerLiteApi\Api\Groups;
use MailerLiteApi\MailerLite;

/**
 * Mailerlite integration class.
 */
class MockMailerliteClient implements Client_Interface {

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
  public function set_config() {
    // do nothing.
  }

  /**
   * Returns the build client
   *
   * @return object
   */
  public function get_client() {
    return $this->client;
  }
}
