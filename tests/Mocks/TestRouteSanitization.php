<?php
/**
 * Endpoint for testing Base_Route sanitization of fields.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/test-route-sanitization
 *
 * @package Eightshift_Forms\Rest
 */

declare( strict_types=1 );

namespace EightshiftFormsTests\Mocks;

use EightshiftForms\Hooks\Filters;
use Eightshift_Libs\Core\Config_Data;
use Eightshift_Forms\Captcha\Basic_Captcha;
use Eightshift_Forms\Exception\Unverified_Request_Exception;
use Eightshift_Forms\Integrations\Authorization\Authorization_Interface;
use Eightshift_Forms\Rest\Base_Route;

/**
 * Class TestRouteSanitization
 */
class TestRouteSanitization extends Base_Route implements Filters {

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
   * @param Authorization_Interface $hmac          Authorization object.
   * @param Basic_Captcha           $basic_captcha Basic_Captcha object.
   */
  public function __construct(
    Config_Data $config,
    Authorization_Interface $hmac,
    Basic_Captcha $basic_captcha
  ) {
    $this->config        = $config;
    $this->hmac          = $hmac;
    $this->basic_captcha = $basic_captcha;
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
  public function route_callback( \WP_REST_Request $request ) {

    try {
      $params = $this->verify_request( $request );
    } catch ( Unverified_Request_Exception $e ) {
      return rest_ensure_response( $e->get_data() );
    }

    $params = $this->unset_irrelevant_params( $params );

    $mock_response = [
      'message' => 'all good',
      'received-params' => $params,
    ];

    return \rest_ensure_response(
      [
        'code' => 200,
        'data' => $mock_response,
      ]
    );
  }
}
