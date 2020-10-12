<?php namespace EightshiftFormsTests\Routes;

use Eightshift_Forms\Core\Actions;
use Eightshift_Forms\Integrations\Authorization\HMAC;
use Eightshift_Forms\Rest\Buckaroo_Response_Handler_Route;
use Eightshift_Forms\Core\Filters;

class BuckarooResponseHandlerRouteTest extends BaseRouteTest
{
  protected function getRouteName(): string {
    return Buckaroo_Response_Handler_Route::class;
  }

  /**
   * Mocking that a certain filter exists. See documentation of Brain Monkey:
   * https://brain-wp.github.io/BrainMonkey/docs/wordpress-hooks-added.html
   *
   * We can't return any actual value, we can just "mock register" this filter.
   *
   * @return void
   */
  protected function addHooks() {
    add_filter( Filters::BUCKAROO, function($key) {
      return $key;
    }, 1, 1);

    add_action( Actions::BUCKAROO_RESPONSE_HANDLER, function($key) {
      return $key;
    }, 1, 1);
  }

  /**
   * Correct request should result in 200 response
   *
   * @return void
   */
  public function testRestCallSuccessful()
  {
    $this->addHooks();

    $request = new \WP_REST_Request('GET', $this->route_endpoint->get_route_uri());
    $request->params['GET'] = [
      $this->route_endpoint::REDIRECT_URL_PARAM => 'http://someurl-com',
      $this->route_endpoint::REDIRECT_URL_CANCEL_PARAM => 'http://someurl-com',
      $this->route_endpoint::REDIRECT_URL_ERROR_PARAM => 'http://someurl-com',
      $this->route_endpoint::REDIRECT_URL_REJECT_PARAM => 'http://someurl-com',
      $this->route_endpoint::STATUS_PARAM => 'success',
    ];
    $request->params['POST'] = [
      $this->route_endpoint::BUCKAROO_RESPONSE_CODE_PARAM => 190,
    ];
    $request->params['GET'][ HMAC::AUTHORIZATION_KEY ] = $this->hmac->generate_hash($request->get_params(), \apply_filters( Filters::BUCKAROO, 'secret_key' ) );
    $response = $this->route_endpoint->route_callback( $request );

    $this->verifyProperlyFormattedResponse($response);
    $this->assertEquals(200, $response->data['code'], $response->data['data']['message']);
  }

  /**
   * We expect an error when we're missing Buckaroo required keys
   *
   * @return void
   */
  public function testRestCallFailsWhenMissingBuckarooKeys()
  {
    $this->addHooks();

    $request = new \WP_REST_Request('GET', $this->route_endpoint->get_route_uri());
    $request->params['GET'] = [
      $this->route_endpoint::REDIRECT_URL_PARAM => 'http://someurl-com',
      $this->route_endpoint::REDIRECT_URL_CANCEL_PARAM => 'http://someurl-com',
      $this->route_endpoint::REDIRECT_URL_ERROR_PARAM => 'http://someurl-com',
      $this->route_endpoint::REDIRECT_URL_REJECT_PARAM => 'http://someurl-com',
      $this->route_endpoint::STATUS_PARAM => 'success',
    ];
    $request->params['GET'][ HMAC::AUTHORIZATION_KEY ] = $this->hmac->generate_hash($request->get_params(), \apply_filters( Filters::BUCKAROO, 'secret_key' ) );
    $response = $this->route_endpoint->route_callback( $request );

    $this->verifyProperlyFormattedError($response);
    $this->assertNotEquals(200, $response->data['code']);
  }

  /**
   * We expect an error when we're missing Buckaroo required keys
   *
   * @return void
   */
  public function testCustomActionRanWhenDefined()
  {
    $this->addHooks();

    $request = new \WP_REST_Request('GET', $this->route_endpoint->get_route_uri());
    $request->params['GET'] = [
      $this->route_endpoint::REDIRECT_URL_PARAM => 'http://someurl-com',
      $this->route_endpoint::REDIRECT_URL_CANCEL_PARAM => 'http://someurl-com',
      $this->route_endpoint::REDIRECT_URL_ERROR_PARAM => 'http://someurl-com',
      $this->route_endpoint::REDIRECT_URL_REJECT_PARAM => 'http://someurl-com',
      $this->route_endpoint::STATUS_PARAM => 'success',
    ];
    $request->params['POST'] = [
      $this->route_endpoint::BUCKAROO_RESPONSE_CODE_PARAM => 190,
    ];
    $request->params['GET'][ HMAC::AUTHORIZATION_KEY ] = $this->hmac->generate_hash($request->get_params(), \apply_filters( Filters::BUCKAROO, 'secret_key' ) );
    $response = $this->route_endpoint->route_callback( $request );

    $this->assertSame( 1, did_action( Actions::BUCKAROO_RESPONSE_HANDLER ) );
  }

  /**
   * We expect an error when we're missing Buckaroo required keys
   *
   * @return void
   */
  public function testUserWasRedirectedIfRequestWasOk()
  {
    $this->addHooks();

    $request = new \WP_REST_Request('GET', $this->route_endpoint->get_route_uri());
    $request->params['GET'] = [
      $this->route_endpoint::REDIRECT_URL_PARAM => 'http://someurl-com',
      $this->route_endpoint::REDIRECT_URL_CANCEL_PARAM => 'http://someurl-com',
      $this->route_endpoint::REDIRECT_URL_ERROR_PARAM => 'http://someurl-com',
      $this->route_endpoint::REDIRECT_URL_REJECT_PARAM => 'http://someurl-com',
      $this->route_endpoint::STATUS_PARAM => 'success',
    ];
    $request->params['POST'] = [
      $this->route_endpoint::BUCKAROO_RESPONSE_CODE_PARAM => 190,
    ];
    $request->params['GET'][ HMAC::AUTHORIZATION_KEY ] = $this->hmac->generate_hash($request->get_params(), \apply_filters( Filters::BUCKAROO, 'secret_key' ) );
    $response = $this->route_endpoint->route_callback( $request );

    $this->assertSame( 1, did_action( self::WP_REDIRECT_ACTION ) );
  }

}