<?php

/**
 * Mailchimp marketing client implementation
 *
 * @package Eightshift_Forms\Integrations\Mailchimp
 */

declare( strict_types=1 );

namespace EightshiftFormsTests\Mocks;

use Eightshift_Forms\Integrations\Mailchimp\Mailchimp_Marketing_Client_Interface;
use \MailchimpMarketing\ApiClient as MarketingApiClient;
use \MailchimpMarketing\Api\ListsApi;
use Codeception\Stub;
use EightshiftFormsTests\Integrations\Mailchimp\DataProvider;
use \GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * Mailchimp integration class.
 */
class MockMailchimpMarketingClient implements Mailchimp_Marketing_Client_Interface {

  /**
   * Constructs object
   */
  public function __construct() {
    $this->client = Stub::make(MarketingApiClient::class, [
      'lists' => Stub::make(ListsApi::class, [
        'setListMember' => function( $list_id, $subscriber_hash, $params ) {
          if ( $list_id === DataProvider::INVALID_LIST_ID ) {
            throw new ClientException( 'invalid list id', new Request('GET', 'test'), new Response() );
          }

          return DataProvider::getMockAddOrUpdateMemberResponse([
            'listId' => $list_id,
            'email' => DataProvider::MOCK_EMAIL,
            'mergeFields' => $params['merge_fields'],
          ]);
        },
        'addListMember' => function( $list_id, $params ) {
          if ( $list_id === DataProvider::INVALID_LIST_ID ) {
            throw new ClientException( 'invalid list id', new Request('GET', 'test'), new Response() );
          }

          return DataProvider::getMockAddOrUpdateMemberResponse([
            'listId' => $list_id,
            'email' => DataProvider::MOCK_EMAIL,
            'mergeFields' => $params['merge_fields'],
          ]);
        },

        'updateListMemberTags' => function( $list_id, $subscriber_hash, $tags ) {
          return '';
        },

        'setConfig' => function($data) {
          return;
        }
      ]),
    ]);
  }

  /**
   * Mock setting config.
   *
   * @return object
   */
  public function set_config() {
    $this->client->setConfig([]);
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
