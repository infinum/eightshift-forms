<?php namespace EightshiftFormsTests\Routes;

use EightshiftForms\Rest\SendEmailRoute;
class SendEmailRouteTest extends BaseRouteTest
{
  const METHOD = 'POST';

  protected function getRouteName(): string {
    return SendEmailRoute::class;
  }

  /**
   * Correct request should result in 200 response
   *
   * @return void
   */
  public function testRestCallSuccessful()
  {
    $request = new \WP_REST_Request(self::METHOD, $this->route_endpoint->getRouteUri());
    $request->params[self::METHOD] = [
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
    $request = new \WP_REST_Request(self::METHOD, $this->route_endpoint->getRouteUri());
    $request->params[self::METHOD] = [
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
    $request = new \WP_REST_Request(self::METHOD, $this->route_endpoint->getRouteUri());
    $request->params[self::METHOD] = [
      $this->route_endpoint::TO_PARAM => 'to param',
      $this->route_endpoint::SUBJECT_PARAM => 'subject',
      $this->route_endpoint::MESSAGE_PARAM => '',
    ];

    $response = $this->route_endpoint->route_callback( $request );

    $this->verifyProperlyFormattedResponse($response);
    $this->assertEquals(400, $response->data['code'] );
    $this->assertSame( 0, did_action( self::WP_MAIL_ACTION ) );

    $request->params[self::METHOD] = [
      $this->route_endpoint::TO_PARAM => 'to param',
      $this->route_endpoint::SUBJECT_PARAM => '',
      $this->route_endpoint::MESSAGE_PARAM => 'message',
    ];

    $response = $this->route_endpoint->route_callback( $request );

    $this->verifyProperlyFormattedResponse($response);
    $this->assertEquals(400, $response->data['code'] );
    $this->assertSame( 0, did_action( self::WP_MAIL_ACTION ) );

    $request->params[self::METHOD] = [
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