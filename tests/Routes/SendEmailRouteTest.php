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
    $request = new \WP_REST_Request('POST', $this->route_endpoint->get_route_uri());
    $request->params['POST'] = [
      $this->route_endpoint::TO_PARAM => 'some value',
      $this->route_endpoint::SUBJECT_PARAM => 'some value',
      $this->route_endpoint::MESSAGE_PARAM => 'some value',
      'nonce' => 'some value',
      'form-unique-id' => 'some-d',
    ];
    $response = $this->route_endpoint->route_callback( $request );

    $this->verifyProperlyFormattedResponse($response);
    $this->assertEquals(200, $response->data['code'] );
    $this->assertSame( 1, did_action( self::WP_MAIL_ACTION ) );
  }

  /**
   * Correct request should result in 200 response
   *
   * @return void
   */
  public function testRestCallSuccessfulWithPlaceholders()
  {
    $request = new \WP_REST_Request('POST', $this->route_endpoint->get_route_uri());
    $request->params['POST'] = [
      $this->route_endpoint::TO_PARAM => 'to param',
      $this->route_endpoint::SUBJECT_PARAM => 'subject',
      $this->route_endpoint::MESSAGE_PARAM => 'Message [[message]]',
      'nonce' => 'some value',
      'form-unique-id' => 'some-d',
    ];

    $response = $this->route_endpoint->route_callback( $request );

    $this->verifyProperlyFormattedResponse($response);
    $this->assertEquals(200, $response->data['code'] );
    $this->assertSame( 1, did_action( self::WP_MAIL_ACTION ) );
  }

  /**
   * If any of the required params (to, subject, message) is empty, wp_mail will fail.
   *
   * @return void
   */
  public function testRestCallFailsIfRequiredParamsEmpty()
  {
    $request = new \WP_REST_Request('GET', $this->route_endpoint->get_route_uri());
    $request->params['GET'] = [
      $this->route_endpoint::TO_PARAM => 'to param',
      $this->route_endpoint::SUBJECT_PARAM => 'subject',
      $this->route_endpoint::MESSAGE_PARAM => '',
    ];

    $response = $this->route_endpoint->route_callback( $request );

    $this->verifyProperlyFormattedResponse($response);
    $this->assertEquals(400, $response->data['code'] );
    $this->assertSame( 0, did_action( self::WP_MAIL_ACTION ) );

    $request->params['GET'] = [
      $this->route_endpoint::TO_PARAM => 'to param',
      $this->route_endpoint::SUBJECT_PARAM => '',
      $this->route_endpoint::MESSAGE_PARAM => 'message',
    ];

    $response = $this->route_endpoint->route_callback( $request );

    $this->verifyProperlyFormattedResponse($response);
    $this->assertEquals(400, $response->data['code'] );
    $this->assertSame( 0, did_action( self::WP_MAIL_ACTION ) );

    $request->params['GET'] = [
      $this->route_endpoint::TO_PARAM => '',
      $this->route_endpoint::SUBJECT_PARAM => 'subject',
      $this->route_endpoint::MESSAGE_PARAM => 'message',
    ];

    $response = $this->route_endpoint->route_callback( $request );

    $this->verifyProperlyFormattedResponse($response);
    $this->assertEquals(400, $response->data['code'] );
    $this->assertSame( 0, did_action( self::WP_MAIL_ACTION ) );
  }
}