<?php namespace EightshiftFormsTests\Routes;

use Eightshift_Forms\Rest\Test_Route;
use Eightshift_Forms\Captcha\Basic_Captcha;
use Eightshift_Forms\Integrations\Authorization\HMAC;

class TestRouteTest extends BaseRouteTest
{
  protected function getRouteName(): string {
    return Test_Route::class;
  }

  /**
   * Correct request should result in 200 response
   *
   * @return void
   */
  public function testRestCallSuccessful()
  {
    $request = new \WP_REST_Request('GET', $this->route_endpoint->get_route_uri());
    $request->params['GET'] = [
      Test_Route::REQUIRED_PARAMETER_1 => 'some-value',
      Test_Route::REQUIRED_PARAMETER_2 => 'some-value',
    ];
    $request->params['GET'][ HMAC::AUTHORIZATION_KEY ] = $this->hmac->generate_hash($request->params['GET'], Test_Route::TEST_SALT );
    $response = $this->route_endpoint->route_callback( $request );

    $this->verifyProperlyFormattedResponse($response);
    $this->assertEquals(200, $response->data['code'], $response->data['data']['message']);
  }

  /**
   * We expect an error response if we're didn't include all required params.
   *
   * @return void
   */
  public function testRestCallFailsBecauseMissingRequiredParams()
  {
    $request = new \WP_REST_Request('GET', $this->route_endpoint->get_route_uri());
    $request->params['GET'] = [
      Test_Route::REQUIRED_PARAMETER_1 => 'some-value',
    ];
    $request->params['GET'][ HMAC::AUTHORIZATION_KEY ] = $this->hmac->generate_hash($request->params['GET'], Test_Route::TEST_SALT );
    $response = $this->route_endpoint->route_callback( $request );

    $this->verifyProperlyFormattedError($response);
    $this->assertNotEquals(200, $response->data['code']);
  }

  /**
   * We expect an error response if we're didn't send the correct authorization param (and authorization
   * is enabled for the route)
   *
   * @return void
   */
  public function testRestCallFailsBecauseMissingAuthorization()
  {

    $request = new \WP_REST_Request('GET', $this->route_endpoint->get_route_uri());
    $request->params['GET'] = [
      Test_Route::REQUIRED_PARAMETER_1 => 'some-value',
      Test_Route::REQUIRED_PARAMETER_2 => 'some-value',
    ];
    $response = $this->route_endpoint->route_callback( $request );

    $this->verifyProperlyFormattedError($response);
    $this->assertNotEquals(200, $response->data['code']);
  }

  /**
   * If any of the basic captcha fields is sent but the math does not add up, we expect an error response.
   *
   * @return void
   */
  public function testRestCallFailsBecauseWrongCaptchaAnswer()
  {

    $request = new \WP_REST_Request('GET', $this->route_endpoint->get_route_uri());
    $request->params['GET'] = [
      Test_Route::REQUIRED_PARAMETER_1 => 'some-value',
      Basic_Captcha::FIRST_NUMBER_KEY => 2,
      Basic_Captcha::SECOND_NUMBER_KEY => 2,
      Basic_Captcha::RESULT_KEY => 5,
    ];
    $request->params['GET'][ HMAC::AUTHORIZATION_KEY ] = $this->hmac->generate_hash($request->params['GET'], Test_Route::TEST_SALT );
    $response = $this->route_endpoint->route_callback( $request );

    $this->verifyProperlyFormattedError($response);
    $this->assertEquals(429, $response->data['code']);
  }

  /**
   * If any of the basic captcha fields is sent and the math adds up, we expect everything to be ok.
   *
   * @return void
   */
  public function testRestCallSucceedsOnCorrectCaptchaAnswer()
  {

    $request = new \WP_REST_Request('GET', $this->route_endpoint->get_route_uri());
    $request->params['GET'] = [
      Test_Route::REQUIRED_PARAMETER_1 => 'some-value',
      Test_Route::REQUIRED_PARAMETER_2 => 'some-value',
      Basic_Captcha::FIRST_NUMBER_KEY => 2,
      Basic_Captcha::SECOND_NUMBER_KEY => 2,
      Basic_Captcha::RESULT_KEY => 4,
    ];
    $request->params['GET'][ HMAC::AUTHORIZATION_KEY ] = $this->hmac->generate_hash($request->params['GET'], Test_Route::TEST_SALT );
    $response = $this->route_endpoint->route_callback( $request );

    $this->verifyProperlyFormattedResponse($response);
    $this->assertEquals(200, $response->data['code'], $response->data['data']['message']);
  }

  /**
   * If we provide $this->get_irrelevant_params(), those params will be unset from the request.
   *
   * @return void
   */
  public function testRouteUnsetsIrrelevantParams()
  {

    $request = new \WP_REST_Request('GET', $this->route_endpoint->get_route_uri());
    $request->params['GET'] = [
      Test_Route::REQUIRED_PARAMETER_1 => 'some-value',
      Test_Route::REQUIRED_PARAMETER_2 => 'some-value',
      Test_Route::IRRELEVANT_PARAM => 'some-irrelevant-value',
    ];
    $request->params['GET'][ HMAC::AUTHORIZATION_KEY ] = $this->hmac->generate_hash($request->params['GET'], Test_Route::TEST_SALT );
    $response = $this->route_endpoint->route_callback( $request );

    $this->verifyProperlyFormattedResponse($response);
    $this->assertArrayNotHasKey(Test_Route::IRRELEVANT_PARAM, $response->data['data']['received-params'] );
    $this->assertEquals(200, $response->data['code'], $response->data['data']['message']);
  }
}