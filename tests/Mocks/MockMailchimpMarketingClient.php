<?php

/**
 * Mailchimp marketing client implementation
 *
 * @package EightshiftForms\Integrations\Mailchimp
 */

declare( strict_types=1 );

namespace EightshiftFormsTests\Mocks;

use EightshiftForms\Integrations\ClientInterface;
use \MailchimpMarketing\ApiClient as MarketingApiClient;
use \MailchimpMarketing\Api\ListsApi;
use Codeception\Stub;
use EightshiftFormsTests\Integrations\Mailchimp\DataProvider;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * Mailchimp integration class.
 */
class MockMailchimpMarketingClient implements ClientInterface {

	/**
	 * Constructs object
	 */
	public function __construct() {
    $this->client = Stub::make(MarketingApiClient::class, [
      'lists' => Stub::make(ListsApi::class, [
        'setListMember' => function( $listId, $subscriberHash, $params ) {
          if ( $listId === DataProvider::INVALID_LIST_ID ) {
            throw new ClientException( 'invalid list id', new Request('GET', 'test'), new Response() );
          }

          return DataProvider::getMockAddOrUpdateMemberResponse([
            'list_id' => $listId,
            'email' => DataProvider::MOCK_EMAIL,
            'merge_fields' => $params['merge_fields'],
          ]);
        },
        'addListMember' => function( $listId, $params ) {
          if ( $listId === DataProvider::INVALID_LIST_ID ) {
            throw new ClientException( 'invalid list id', new Request('GET', 'test'), new Response() );
          }

          return DataProvider::getMockAddOrUpdateMemberResponse([
            'list_id' => $listId,
            'email' => DataProvider::MOCK_EMAIL,
            'merge_fields' => $params['merge_fields'],
          ]);
        },

        'updateListMemberTags' => function( $listId, $subscriberHash, $tags ) {
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
	public function setConfig() {
    $this->client->setConfig([]);
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
