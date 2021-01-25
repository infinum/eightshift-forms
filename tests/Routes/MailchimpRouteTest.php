<?php namespace EightshiftFormsTests\Routes;

use Eightshift_Forms\Hooks\Filters;
use Eightshift_Forms\Rest\Mailchimp_Route;
use EightshiftFormsTests\Integrations\Mailchimp\DataProvider;

class MailchimpRouteTest extends BaseRouteTest
{
  protected function getRouteName(): string {
    return Mailchimp_Route::class;
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
    $request = new \WP_REST_Request('GET', $this->route_endpoint->get_route_uri());
    $request->params['GET'] = [
      $this->route_endpoint::EMAIL_PARAM => 'someemail@infinum.com',
      $this->route_endpoint::LIST_ID_PARAM => 'list-id',
      'nonce' => 'asdb',
      'form-unique-id' => '123'
    ];
    $response = $this->route_endpoint->route_callback( $request );

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
    $request = new \WP_REST_Request('GET', $this->route_endpoint->get_route_uri());
    $request->params['GET'] = [
      $this->route_endpoint::EMAIL_PARAM => 'someemail@infinum.com',
      $this->route_endpoint::LIST_ID_PARAM => DataProvider::INVALID_LIST_ID,
    ];
    $response = $this->route_endpoint->route_callback( $request );

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
    $request = new \WP_REST_Request('GET', $this->route_endpoint->get_route_uri());
    $request->params['GET'] = [
      $this->route_endpoint::EMAIL_PARAM => 'someemail@infinum.com',
      $this->route_endpoint::LIST_ID_PARAM => 'list-id',
      $this->route_endpoint::TAGS_PARAM => [
        'aaa',
        'bbb',
        'ccc',
      ],
      'nonce' => 'asdb',
      'form-unique-id' => '123'
    ];
    $response = $this->route_endpoint->route_callback( $request );

    $this->verifyProperlyFormattedResponse($response);
    $this->assertEquals(200, $response->data['code'] );
  }
}