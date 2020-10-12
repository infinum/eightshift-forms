<?php namespace EightshiftFormsTests\Routes;

use Eightshift_Forms\Integrations\Authorization\HMAC;
use Eightshift_Forms\Rest\Buckaroo_Response_Handler_Route;

class BuckarooResponseHandlerRouteTest extends BaseRouteTest
{
  protected function get_route_name() {
    return Buckaroo_Response_Handler_Route::class;
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
      // Test_Route::REQUIRED_PARAMETER_1 => 'some-value',
      // Test_Route::REQUIRED_PARAMETER_2 => 'some-value',
    ];
    // $request->params['GET'][ HMAC::AUTHORIZATION_KEY ] = $this->hmac->generate_hash($request->params['GET'], Test_Route::TEST_SALT );
    $response = $this->route_endpoint->route_callback( $request );
  }

}