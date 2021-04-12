<?php
/**
 * Base route (should be extend) for Buckaroo routes.
 *
 * @package Eightshift_Forms\Rest
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Rest;

use Eightshift_Forms\Integrations\Buckaroo\Buckaroo;
use Eightshift_Forms\Integrations\Authorization\Authorization_Interface;
use Eightshift_Libs\Core\Config_Data;
use Eightshift_Forms\Hooks\Filters;
use Eightshift_Forms\Captcha\Basic_Captcha;
use Eightshift_Forms\Integrations\Authorization\HMAC;

/**
 * Class Base_Buckaroo_Route
 */
abstract class Base_Buckaroo_Route extends Base_Route implements Filters {

  /**
   * Issuer, bank code.
   *
   * @var string
   */
  const ISSUER_PARAM = 'issuer';

  /**
   * Param for description of this transaction. Not used in Emandates because Emandates
   * has it's own field / param for this: (see buckaroo-emandate-route).
   *
   * @var string
   */
  const PAYMENT_DESCRIPTION_PARAM = 'payment-description';

  /**
   * Test param, set if you wish to Test Buckaroo implementation.
   *
   * @var string
   */
  const TEST_PARAM = 'test';

  /**
   * Name of the required parameter for redirect url.
   *
   * @var string
   */
  const REDIRECT_URL_PARAM = 'redirect-url';

  /**
   * Name of the required parameter for redirect url cancel.
   *
   * @var string
   */
  const REDIRECT_URL_CANCEL_PARAM = 'redirect-url-cancel';

  /**
   * Name of the required parameter for redirect url error.
   *
   * @var string
   */
  const REDIRECT_URL_ERROR_PARAM = 'redirect-url-error';

  /**
   * Name of the required parameter for redirect url reject.
   *
   * @var string
   */
  const REDIRECT_URL_REJECT_PARAM = 'redirect-url-reject';

  /**
   * Config data obj.
   *
   * @var Config_Data
   */
  protected $config;

  /**
   * Buckaroo integration obj.
   *
   * @var Buckaroo
   */
  protected $buckaroo;

  /**
   * Buckaroo Response Handler Route obj.
   *
   * @var Buckaroo_Response_Handler_Route
   */
  protected $buckaroo_response_handler_route;

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
   * @param Config_Data                     $config                          Config data obj.
   * @param Buckaroo                        $buckaroo                        Buckaroo integration obj.
   * @param Buckaroo_Response_Handler_Route $buckaroo_response_handler_route Response handler route obj.
   * @param Authorization_Interface         $hmac                            Authorization object.
   * @param Basic_Captcha                   $basic_captcha                   Basic_Captcha object.
   */
  public function __construct(
    Config_Data $config,
    Buckaroo $buckaroo,
    Buckaroo_Response_Handler_Route $buckaroo_response_handler_route,
    Authorization_Interface $hmac,
    Basic_Captcha $basic_captcha
  ) {
    $this->config                          = $config;
    $this->buckaroo                        = $buckaroo;
    $this->buckaroo_response_handler_route = $buckaroo_response_handler_route;
    $this->hmac                            = $hmac;
    $this->basic_captcha                   = $basic_captcha;
  }

  /**
   * We need to define redirect URLs so that Buckaroo redirects the user to our buckaroo-response-handler route
   * which might run some custom logic and then redirect the user to the actual redirect URL as defined in the form's
   * options.
   *
   * @param array $params Array of WP_REST_Request params.
   * @return array
   */
  protected function set_redirect_urls( array $params ): array {

    // Now let's define all Buckaroo-recognized statuses for which we need to provide redirect URLs.
    $statuses = [
      Buckaroo_Response_Handler_Route::STATUS_SUCCESS,
      Buckaroo_Response_Handler_Route::STATUS_CANCELED,
      Buckaroo_Response_Handler_Route::STATUS_ERROR,
      Buckaroo_Response_Handler_Route::STATUS_REJECT,
    ];

    // Now let's build redirect URLs (to buckaroo-response-handler middleware route) for each status.
    $redirect_urls = [];
    $base_url      = \home_url( $this->buckaroo_response_handler_route->get_route_uri() );
    foreach ( $statuses as $status_value ) {
      $url_params = $params;
      $url_params[ Buckaroo_Response_Handler_Route::STATUS_PARAM ] = $status_value;

      // We need to encode all params to ensure they're sent properly.
      $url_params = $this->urlencode_params( $url_params );

      // As the last step, add the authorization hash which verifies that the request was not tampered with.
      $url = \add_query_arg( array_merge(
        $url_params,
        [ HMAC::AUTHORIZATION_KEY => rawurlencode( $this->hmac->generate_hash( $url_params, $this->generate_authorization_salt_for_response_handler() ) ) ]
      ), $base_url );

      $redirect_urls[] = $url;
    }

    $this->buckaroo->set_redirect_urls( ...$redirect_urls );

    return $params;
  }

  /**
   * Set Buckaroo to test mode if test param provided.
   *
   * @param array $params Request params.
   * @return void
   */
  protected function set_test_if_needed( array $params ): void {
    if ( isset( $params[ self::TEST_PARAM ] ) && filter_var( $params[ self::TEST_PARAM ], FILTER_VALIDATE_BOOL ) ) {
      $this->buckaroo->set_test();
    }
  }

  /**
   * Define authorization salt used for request to response handler.
   *
   * @return string
   */
  protected function generate_authorization_salt_for_response_handler(): string {
    return \apply_filters( self::BUCKAROO, 'secret_key' ) ?? 'invalid-salt-for-buckaroo-handler';
  }

  /**
   * Toggle if this route requires nonce verification
   *
   * @return bool
   */
  protected function requires_nonce_verification(): bool {
    return true;
  }

  /**
   * Returns allowed methods for this route.
   *
   * @return string|array
   */
  protected function get_methods() {
    return static::CREATABLE;
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
  abstract public function route_callback( \WP_REST_Request $request );

}
