<?php namespace EightshiftFormsTests\Routes;

use EightshiftFormsTests\Mocks\TestRoute;
use EightshiftForms\Captcha\BasicCaptcha;
use EightshiftForms\Integrations\Authorization\Hmac;

class TestRouteTest extends BaseRouteTest
{
  protected function getRouteName(): string {
    return TestRoute::class;
  }

  /**
   * Correct request should result in 200 response
   *
   * @return void
   */
  public function testRestCallSuccessful()
  {
    $request = new \WP_REST_Request('GET', $this->routeEndpoint->getRouteUri());
    $request->params['GET'] = [
      TestRoute::REQUIRED_PARAMETER_1 => 'some-value',
      TestRoute::REQUIRED_PARAMETER_2 => 'some-value',
    ];
    $request->params['GET'][ Hmac::AUTHORIZATION_KEY ] = $this->hmac->generateHash($request->params['GET'], TestRoute::TEST_SALT );
    $response = $this->routeEndpoint->routeCallback( $request );

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
    $request = new \WP_REST_Request('GET', $this->routeEndpoint->getRouteUri());
    $request->params['GET'] = [
      TestRoute::REQUIRED_PARAMETER_1 => 'some-value',
    ];
    $request->params['GET'][ Hmac::AUTHORIZATION_KEY ] = $this->hmac->generateHash($request->params['GET'], TestRoute::TEST_SALT );
    $response = $this->routeEndpoint->routeCallback( $request );

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

    $request = new \WP_REST_Request('GET', $this->routeEndpoint->getRouteUri());
    $request->params['GET'] = [
      TestRoute::REQUIRED_PARAMETER_1 => 'some-value',
      TestRoute::REQUIRED_PARAMETER_2 => 'some-value',
    ];
    $response = $this->routeEndpoint->routeCallback( $request );

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

    $request = new \WP_REST_Request('GET', $this->routeEndpoint->getRouteUri());
    $request->params['GET'] = [
      TestRoute::REQUIRED_PARAMETER_1 => 'some-value',
      BasicCaptcha::FIRST_NUMBER_KEY => 2,
      BasicCaptcha::SECOND_NUMBER_KEY => 2,
      BasicCaptcha::RESULT_KEY => 5,
    ];
    $request->params['GET'][ Hmac::AUTHORIZATION_KEY ] = $this->hmac->generateHash($request->params['GET'], TestRoute::TEST_SALT );
    $response = $this->routeEndpoint->routeCallback( $request );

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

    $request = new \WP_REST_Request('GET', $this->routeEndpoint->getRouteUri());
    $request->params['GET'] = [
      TestRoute::REQUIRED_PARAMETER_1 => 'some-value',
      TestRoute::REQUIRED_PARAMETER_2 => 'some-value',
      BasicCaptcha::FIRST_NUMBER_KEY => 2,
      BasicCaptcha::SECOND_NUMBER_KEY => 2,
      BasicCaptcha::RESULT_KEY => 4,
    ];
    $request->params['GET'][ Hmac::AUTHORIZATION_KEY ] = $this->hmac->generateHash($request->params['GET'], TestRoute::TEST_SALT );
    $response = $this->routeEndpoint->routeCallback( $request );

    $this->verifyProperlyFormattedResponse($response);
    $this->assertEquals(200, $response->data['code'], $response->data['data']['message']);
  }

  /**
   * If we provide $this->getIrrelevantParams(), those params will be unset from the request.
   *
   * @return void
   */
  public function testRouteUnsetsIrrelevantParams()
  {

    $request = new \WP_REST_Request('GET', $this->routeEndpoint->getRouteUri());
    $request->params['GET'] = [
      TestRoute::REQUIRED_PARAMETER_1 => 'some-value',
      TestRoute::REQUIRED_PARAMETER_2 => 'some-value',
      TestRoute::IRRELEVANT_PARAM => 'some-irrelevant-value',
    ];
    $request->params['GET'][ Hmac::AUTHORIZATION_KEY ] = $this->hmac->generateHash($request->params['GET'], TestRoute::TEST_SALT );
    $response = $this->routeEndpoint->routeCallback( $request );

    $this->verifyProperlyFormattedResponse($response);
    $this->assertArrayNotHasKey(TestRoute::IRRELEVANT_PARAM, $response->data['data']['received-params'] );
    $this->assertEquals(200, $response->data['code'], $response->data['data']['message']);
  }
}