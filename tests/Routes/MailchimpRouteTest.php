<?php namespace EightshiftFormsTests\Routes;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Rest\MailchimpRoute;
use EightshiftFormsTests\Integrations\Mailchimp\DataProvider;

class MailchimpRouteTest extends BaseRouteTest
{
  const METHOD = 'POST';

  protected function getRouteName(): string {
    return MailchimpRoute::class;
  }

  protected function _before()
  {
    parent::_before();
    add_filter( Filters::MAILCHIMP, function($key) {
      return $key;
    }, 1, 1);
  }

  /**
   * Correct request should result in 200 response
   *
   * @return void
   */
  public function testRestCallSuccessfulWhenAddingNewMembers()
  {
    $request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());
    $request->params[self::METHOD] = [
      $this->routeEndpoint::EMAIL_PARAM => 'someemail@infinum.com',
      $this->routeEndpoint::LIST_ID_PARAM => 'list-id',
      'nonce' => 'asdb',
      'form-unique-id' => '123'
    ];
    $response = $this->routeEndpoint->routeCallback( $request );

    $this->verifyProperlyFormattedResponse($response);
    $this->assertEquals(200, $response->data['code'] );
  }

  /**
   * Invalid list ID should trigger an error response.
   *
   * @return void
   */
  public function testRestCallFailsIfInvalidListId()
  {
    $request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());
    $request->params[self::METHOD] = [
      $this->routeEndpoint::EMAIL_PARAM => 'someemail@infinum.com',
      $this->routeEndpoint::LIST_ID_PARAM => DataProvider::INVALID_LIST_ID,
    ];
    $response = $this->routeEndpoint->routeCallback( $request );

    $this->verifyProperlyFormattedResponse($response);
    $this->assertEquals(400, $response->data['code'] );
  }

  /**
   * Correct request should result in 200 response
   *
   * @return void
   */
  public function testRestCallSuccessfulWhenAddingTags()
  {
    $request = new \WP_REST_Request(self::METHOD, $this->routeEndpoint->getRouteUri());
    $request->params[self::METHOD] = [
      $this->routeEndpoint::EMAIL_PARAM => 'someemail@infinum.com',
      $this->routeEndpoint::LIST_ID_PARAM => 'list-id',
      $this->routeEndpoint::TAGS_PARAM => [
        'aaa',
        'bbb',
        'ccc',
      ],
      'nonce' => 'asdb',
      'form-unique-id' => '123'
    ];
    $response = $this->routeEndpoint->routeCallback( $request );

    $this->verifyProperlyFormattedResponse($response);
    $this->assertEquals(200, $response->data['code'], $response->data['data']['error'] ?? 'Unknown error' );
  }
}