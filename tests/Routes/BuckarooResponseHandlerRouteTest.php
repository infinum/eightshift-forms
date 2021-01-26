<?php namespace EightshiftFormsTests\Routes;

use Eightshift_Forms\Integrations\Authorization\HMAC;
use Eightshift_Forms\Rest\Buckaroo_Response_Handler_Route;
use Eightshift_Forms\Hooks\Filters;
use Eightshift_Forms\Hooks\Actions;
use EightshiftFormsTests\Integrations\Buckaroo\DataProvider;
use Brain\Monkey\Filters as BrainFilters;

class BuckarooResponseHandlerRouteTest extends BaseRouteTest implements Filters, Actions
{
  const METHOD = 'GET';

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
    add_filter( self::BUCKAROO, function($key) {
      return $key;
    }, 1, 1);

    add_action( self::BUCKAROO_RESPONSE_HANDLER, function($key) {
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

    $request = new \WP_REST_Request(self::METHOD, $this->route_endpoint->get_route_uri());
    $request->params[self::METHOD] = [
      $this->route_endpoint::REDIRECT_URL_PARAM => 'http://someurl.com',
      $this->route_endpoint::REDIRECT_URL_CANCEL_PARAM => 'http://someurl.com',
      $this->route_endpoint::REDIRECT_URL_ERROR_PARAM => 'http://someurl.com',
      $this->route_endpoint::REDIRECT_URL_REJECT_PARAM => 'http://someurl.com',
      $this->route_endpoint::STATUS_PARAM => 'success',
    ];
    $request->params['POST'] = DataProvider::idealSuccessResponseMock();

    // We need to URL encode params before calculating the hash (because that is done in the route before
    // verifying the hash. However we can't send the URLs encoded because that won't work. In the app these are sent
    // (encoded) to Buckaroo which will decode them when redirecting back to the response handler.
    $request->params[self::METHOD][ HMAC::AUTHORIZATION_KEY ] = $this->hmac->generate_hash(
      $this->urlencode_params($request->get_query_params()),
      \apply_filters( self::BUCKAROO, 'secret_key' )
    );
    $response = $this->route_endpoint->route_callback( $request );

    $this->verifyProperlyFormattedResponse($response);
    $this->assertEquals(200, $response->data['code'], $response->data['message'] ?? 'message not defined');
  }

  /**
   * We expect an error when we're missing Buckaroo required keys
   *
   * @return void
   */
  public function testRestCallFailsWhenMissingBuckarooKeys()
  {
    $this->addHooks();

    $request = new \WP_REST_Request(self::METHOD, $this->route_endpoint->get_route_uri());
    $request->params[self::METHOD] = [
      $this->route_endpoint::REDIRECT_URL_PARAM => 'http://someurl.com',
      $this->route_endpoint::REDIRECT_URL_CANCEL_PARAM => 'http://someurl.com',
      $this->route_endpoint::REDIRECT_URL_ERROR_PARAM => 'http://someurl.com',
      $this->route_endpoint::REDIRECT_URL_REJECT_PARAM => 'http://someurl.com',
      $this->route_endpoint::STATUS_PARAM => 'success',
    ];
    $request->params[self::METHOD][ HMAC::AUTHORIZATION_KEY ] = $this->hmac->generate_hash($request->get_query_params(), \apply_filters( self::BUCKAROO, 'secret_key' ) );
    $response = $this->route_endpoint->route_callback( $request );

    $this->verifyProperlyFormattedError($response);
    $this->assertNotEquals(200, $response->data['code'], $response->data['message'] ?? 'message not defined');
  }

  /**
   * We expect an error when we're missing Buckaroo required keys
   *
   * @return void
   */
  public function testCustomActionRanWhenDefined()
  {
    $this->addHooks();

    $request = new \WP_REST_Request(self::METHOD, $this->route_endpoint->get_route_uri());
    $request->params[self::METHOD] = [
      $this->route_endpoint::REDIRECT_URL_PARAM => 'http://someurl.com',
      $this->route_endpoint::REDIRECT_URL_CANCEL_PARAM => 'http://someurl.com',
      $this->route_endpoint::REDIRECT_URL_ERROR_PARAM => 'http://someurl.com',
      $this->route_endpoint::REDIRECT_URL_REJECT_PARAM => 'http://someurl.com',
      $this->route_endpoint::STATUS_PARAM => 'success',
    ];
    $request->params['POST'] = [
      $this->route_endpoint::BUCKAROO_RESPONSE_CODE_PARAM => 190,
    ];
    $request->params[self::METHOD][ HMAC::AUTHORIZATION_KEY ] = $this->hmac->generate_hash(
      $this->urlencode_params($request->get_query_params()),
      \apply_filters( self::BUCKAROO, 'secret_key' )
    );
    $response = $this->route_endpoint->route_callback( $request );

    $this->assertSame( 1, did_action( self::BUCKAROO_RESPONSE_HANDLER ), $response->data['message'] ?? 'message not defined' );
  }

  /**
   * We expect an error when we're missing Buckaroo required keys
   *
   * @return void
   */
  public function testUserWasRedirectedIfRequestWasOk()
  {
    $this->addHooks();

    $request = new \WP_REST_Request(self::METHOD, $this->route_endpoint->get_route_uri());
    $request->params[self::METHOD] = [
      $this->route_endpoint::REDIRECT_URL_PARAM => 'http://someurl.com',
      $this->route_endpoint::REDIRECT_URL_CANCEL_PARAM => 'http://someurl.com',
      $this->route_endpoint::REDIRECT_URL_ERROR_PARAM => 'http://someurl.com',
      $this->route_endpoint::REDIRECT_URL_REJECT_PARAM => 'http://someurl.com',
      $this->route_endpoint::STATUS_PARAM => rawurlencode('success'),
    ];
    $request->params['POST'] = DataProvider::idealSuccessResponseMock();
    $request->params[self::METHOD][ HMAC::AUTHORIZATION_KEY ] = $this->hmac->generate_hash(
      $this->urlencode_params($request->get_query_params()),
      \apply_filters( self::BUCKAROO, 'secret_key' )
    );
    $response = $this->route_endpoint->route_callback( $request );
    $this->assertSame( 1, did_action( self::WP_REDIRECT_ACTION ), $response->data['message'] ?? 'message not defined' );
  }

  /**
   * We expect an error when we're missing Buckaroo required keys
   *
   * @return void
   */
  public function testUserWasRedirectedIfRedirectUrlsMissing()
  {
    $this->addHooks();

    $request = new \WP_REST_Request(self::METHOD, $this->route_endpoint->get_route_uri());
    $request->params[self::METHOD] = [
      $this->route_endpoint::REDIRECT_URL_PARAM => '',
      $this->route_endpoint::REDIRECT_URL_CANCEL_PARAM => '',
      $this->route_endpoint::REDIRECT_URL_ERROR_PARAM => '',
      $this->route_endpoint::REDIRECT_URL_REJECT_PARAM => '',
      $this->route_endpoint::STATUS_PARAM => 'success',
    ];
    $request->params['POST'] = [
      $this->route_endpoint::BUCKAROO_RESPONSE_CODE_PARAM => 190,
    ];
    $request->params[self::METHOD][ HMAC::AUTHORIZATION_KEY ] = $this->hmac->generate_hash(
      $this->urlencode_params($request->get_query_params()),
      \apply_filters( self::BUCKAROO, 'secret_key' )
    );
    $response = $this->route_endpoint->route_callback( $request );

    $this->assertSame( 1, did_action( self::WP_REDIRECT_ACTION ), $response->data['message'] ?? 'message not defined' );
  }

  /**
   * Test redirection works on iDEAL success
   *
   * @return void
   */
  public function testRedirectUrlBuildingWorksOnIdealSuccess()
  {
    $correct_url = 'http://success.com';
    $params = [
      $this->route_endpoint::STATUS_PARAM => $this->route_endpoint::STATUS_SUCCESS,
      $this->route_endpoint::REDIRECT_URL_PARAM => $correct_url,
    ];

    $redirect_url = $this->route_endpoint->build_redirect_url( $params, DataProvider::idealSuccessResponseMock() );
    $this->assertEquals($redirect_url, $correct_url);
  }

  /**
   * Test redirection works on iDEAL error
   *
   * @return void
   */
  public function testRedirectUrlBuildingWorksOnIdeaelError()
  {
    $correct_url = 'http://error.com';
    $params = [
      $this->route_endpoint::STATUS_PARAM => $this->route_endpoint::STATUS_ERROR,
      $this->route_endpoint::REDIRECT_URL_ERROR_PARAM => $correct_url,
    ];

    $redirect_url = $this->route_endpoint->build_redirect_url( $params, DataProvider::idealErrorResponseMock() );
    $this->assertEquals($redirect_url, $correct_url);
  }

  /**
   * Test redirection works on iDEAL reject
   *
   * @return void
   */
  public function testRedirectUrlBuildingWorksOnIdealReject()
  {
    $correct_url = 'http://reject.com';
    $params = [
      $this->route_endpoint::STATUS_PARAM => $this->route_endpoint::STATUS_REJECT,
      $this->route_endpoint::REDIRECT_URL_REJECT_PARAM => $correct_url,
    ];

    $redirect_url = $this->route_endpoint->build_redirect_url( $params, DataProvider::idealRejectResponseMock() );
    $this->assertEquals($redirect_url, $correct_url);
  }

  /**
   * Test redirection works on iDEAL cancel
   *
   * @return void
   */
  public function testRedirectUrlBuildingWorksOnIdealCancel()
  {
    $correct_url = 'http://cancel.com';
    $params = [
      $this->route_endpoint::STATUS_PARAM => $this->route_endpoint::STATUS_CANCELED,
      $this->route_endpoint::REDIRECT_URL_CANCEL_PARAM => $correct_url,
    ];

    $redirect_url = $this->route_endpoint->build_redirect_url( $params, DataProvider::idealCancelledResponseMock() );
    $this->assertEquals($redirect_url, $correct_url);
  }

  /**
   * Test redirection redirects back to homepage if url isn't provided.
   *
   * @return void
   */
  public function testRedirectUrlBuildingWorksWhenUrlNotProvided()
  {
    $params = [
      $this->route_endpoint::STATUS_PARAM => $this->route_endpoint::STATUS_SUCCESS,
    ];

    $redirect_url = $this->route_endpoint->build_redirect_url( $params, DataProvider::idealSuccessResponseMock() );
    $this->assertEquals($redirect_url, self::HOME_URL);
  }


  /**
   * Test redirection works on Emandate success
   *
   * @return void
   */
  public function testRedirectUrlBuildingWorksOnEmandateSuccess()
  {
    $correct_url = 'http://success.com';
    $params = [
      $this->route_endpoint::STATUS_PARAM => $this->route_endpoint::STATUS_SUCCESS,
      $this->route_endpoint::REDIRECT_URL_PARAM => $correct_url,
    ];

    $redirect_url = $this->route_endpoint->build_redirect_url( $params, DataProvider::emandateSuccessResponseMock() );
    $this->assertEquals($redirect_url, $correct_url);
  }

  /**
   * Test redirection works on Emandate error
   *
   * @return void
   */
  public function testRedirectUrlBuildingWorksOnEmandatelError()
  {
    $correct_url = 'http://error.com';
    $params = [
      $this->route_endpoint::STATUS_PARAM => $this->route_endpoint::STATUS_ERROR,
      $this->route_endpoint::REDIRECT_URL_ERROR_PARAM => $correct_url,
    ];

    $redirect_url = $this->route_endpoint->build_redirect_url( $params, DataProvider::emandateFailedResponseMock() );
    $this->assertEquals($redirect_url, $correct_url);
  }

  /**
   * Test redirection works on Emandate cancel
   *
   * @return void
   */
  public function testRedirectUrlBuildingWorksOnEmandateCancel()
  {
    $correct_url = 'http://cancel.com';
    $params = [
      $this->route_endpoint::STATUS_PARAM => $this->route_endpoint::STATUS_CANCELED,
      $this->route_endpoint::REDIRECT_URL_CANCEL_PARAM => $correct_url,
    ];

    $redirect_url = $this->route_endpoint->build_redirect_url( $params, DataProvider::emandateCancelledResponseMock() );
    $this->assertEquals($redirect_url, $correct_url);
  }

  /**
   * Test url was filtered if filter is set in project
   *
   * @return void
   */
  public function testRedirectUrlWasFilteredIfProvided()
  {
    apply_filters(Filters::BUCKAROO_REDIRECT_URL, 'Filter applied', $this);
    $this->route_endpoint->build_redirect_url( [], DataProvider::emandateCancelledResponseMock() );
    $this->assertTrue( BrainFilters\applied( Filters::BUCKAROO_REDIRECT_URL ) > 0 );
  }

  private function urlencode_params( array $params ): array {
    return array_map( function( $param ) {
      return is_string( $param ) ? rawurlencode( $param ) : $param;
    }, $params);
  }

}