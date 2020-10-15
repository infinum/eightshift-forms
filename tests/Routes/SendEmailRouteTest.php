<?php namespace EightshiftFormsTests\Routes;

use Eightshift_Forms\Rest\Send_Email_Route;

class SendEmailRouteTest extends BaseRouteTest
{
  protected function getRouteName(): string {
    return Send_Email_Route::class;
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
      $this->route_endpoint::TO_PARAM => 'some value',
      $this->route_endpoint::SUBJECT_PARAM => 'some value',
      $this->route_endpoint::MESSAGE_PARAM => 'some value',
      $this->route_endpoint::ADDITIONAL_HEADERS_PARAM => 'some value',
    ];
    $response = $this->route_endpoint->route_callback( $request );

    $this->verifyProperlyFormattedResponse($response);
    $this->assertEquals(200, $response->data['code'] );
    $this->assertSame( 1, did_action( self::WP_MAIL_ACTION ) );
  }
}