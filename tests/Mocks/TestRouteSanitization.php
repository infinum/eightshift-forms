<?php
/**
 * Endpoint for testing BaseRoute sanitization of fields.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/test-route-sanitization
 *
 * @package EightshiftForms\Rest
 */

declare( strict_types=1 );

namespace EightshiftFormsTests\Mocks;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Captcha\BasicCaptcha;
use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Integrations\Authorization\AuthorizationInterface;
use EightshiftForms\Rest\BaseRoute;

/**
 * Class TestRouteSanitization
 */
class TestRouteSanitization extends BaseRoute implements Filters {

  /**
   * Route slug
   *
   * @var string
   */
  const ENDPOINT_SLUG = '/test-route-sanitization';


  /**
   * Construct object
   *
   * @param Config_Data             $config        Config data obj.
   * @param AuthorizationInterface $hmac          Authorization object.
   * @param BasicCaptcha           $basicCaptcha BasicCaptcha object.
   */
  public function __construct(
    AuthorizationInterface $hmac,
    BasicCaptcha $basicCaptcha
  ) {
    $this->hmac = $hmac;
    $this->basicCaptcha = $basicCaptcha;
  }

  /**
   * Method that returns rest response
   *
   * @param  \WP_REST_Request $request Data got from endpoint url.
   *
   * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
   *                                is already an instance, WP_HTTP_Response, otherwise
   *                                returns a new WP_REST_Response instance.
   */
  public function routeCallback( \WP_REST_Request $request ) {

    try {
      $params = $this->verifyRequest( $request );
    } catch ( UnverifiedRequestException $e ) {
      return rest_ensure_response( $e->getData() );
    }

    $params = $this->unsetIrrelevantParams( $params );

    $mockResponse = [
      'message' => 'all good',
      'received-params' => $params,
    ];

    return \rest_ensure_response(
      [
        'code' => 200,
        'data' => $mockResponse,
      ]
    );
  }
}
