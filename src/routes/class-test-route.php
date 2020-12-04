<?php
/**
 * Endpoint for testing Base_Route.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/test-route
 *
 * @package Eightshift_Forms\Rest
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Rest;

use Eightshift_Forms\Hooks\Filters;
use Eightshift_Libs\Core\Config_Data;
use Eightshift_Forms\Captcha\Basic_Captcha;
use Eightshift_Forms\Exception\Unverified_Request_Exception;
use Eightshift_Forms\Integrations\Authorization\Authorization_Interface;

/**
 * Class Test_Route
 */
class Test_Route extends Base_Route implements Filters {

  /**
   * Route slug
   *
   * @var string
   */
  const ENDPOINT_SLUG = '/test-route';

  /**
   * Test salt used for this route.
   *
   * @var string
   */
  const TEST_SALT = '1234-test-salt';

  /**
   * Mock of required parameters.
   *
   * @var array
   */
  const REQUIRED_PARAMETER_1 = 'required-param';
  const REQUIRED_PARAMETER_2 = 'required-param-2';
  const IRRELEVANT_PARAM     = 'irrelevant-param';

  /**
   * Config data obj.
   *
   * @var Config_Data
   */
  protected $config;

  /**
   * Implementation of the Authorization obj.
   *
   * @var Authorization_Interface
   */
  protected $hmac;

  /**
   * Basic Captcha object.
   *
   * @var Basic_Captcha
   */
  protected $basic_captcha;

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

  /**
   * Define authorization salt used for request to response handler.
   *
   * @return string
   */
  protected function get_authorization_salt(): string {
    return self::TEST_SALT;
  }

  /**
   * Defines a list of required parameters which must be present in the request or it will error out.
   *
   * @return array
   */
  protected function get_required_params(): array {
    return [
      self::REQUIRED_PARAMETER_1,
      self::REQUIRED_PARAMETER_2,
    ];
  }

  /**
   * Returns keys of irrelevant params which we don't want to send to CRM (even tho they're in form).
   *
   * @return array
   */
  protected function get_irrelevant_params(): array {
    return [
      self::IRRELEVANT_PARAM,
    ];
  }
}
