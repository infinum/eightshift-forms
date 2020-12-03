<?php
/**
 * Endpoint for handling Buckaroo integration on form submit.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/buckaroo-response-handler
 *
 * @package Eightshift_Forms\Rest
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Rest;

use Eightshift_Forms\Buckaroo\Invalid_Buckaroo_Response_Exception;
use Eightshift_Forms\Buckaroo\Response_Factory;
use Eightshift_Forms\Hooks\Actions;
use Eightshift_Forms\Hooks\Filters;
use Eightshift_Libs\Core\Config_Data;
use Eightshift_Forms\Integrations\Buckaroo\Buckaroo;
use Eightshift_Forms\Exception\Unverified_Request_Exception;
use Eightshift_Forms\Integrations\Authorization\Authorization_Interface;

/**
 * Class Buckaroo_Response_Handler_Route
 */
class Buckaroo_Response_Handler_Route extends Base_Route implements Actions, Filters {

  /**
   * Route slug
   *
   * @var string
   */
  const ENDPOINT_SLUG = '/buckaroo-response-handler';

  /**
   * Name of the required parameter for redirect URLs.
   *
   * @var string
   */
  const REDIRECT_URLS_PARAM = 'redirect-urls';

  /**
   * Name of the required parameter for status of Buckaroo transaction.
   *
   * @var string
   */
  const STATUS_PARAM = 'status';

  /**
   * Value of the status param.
   *
   * @var string
   */
  const STATUS_SUCCESS = 'success';

  /**
   * Value of the status param.
   *
   * @var string
   */
  const STATUS_CANCELED = 'canceled';

  /**
   * Value of the status param for error.
   *
   * @var string
   */
  const STATUS_ERROR = 'error';

  /**
   * Value of the status param for reject.
   *
   * @var string
   */
  const STATUS_REJECT = 'reject';

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
   * Name of the required parameter (provided by Buckaroo) indicating response status.
   *
   * @var string
   */
  const BUCKAROO_RESPONSE_CODE_PARAM = 'BRQ_STATUSCODE';

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
   * Implementation of the Authorization obj.
   *
   * @var Authorization_Interface
   */
  protected $hmac;

  /**
   * Construct object
   *
   * @param Config_Data             $config   Config data obj.
   * @param Buckaroo                $buckaroo Buckaroo integration obj.
   * @param Authorization_Interface $hmac     Authorization object.
   */
  public function __construct( Config_Data $config, Buckaroo $buckaroo, Authorization_Interface $hmac ) {
    $this->config   = $config;
    $this->buckaroo = $buckaroo;
    $this->hmac     = $hmac;
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
      $params          = $this->verify_request( $request, Filters::BUCKAROO );
      $buckaroo_params = $request->get_body_params();
    } catch ( Unverified_Request_Exception $e ) {
      return rest_ensure_response( $e->get_data() );
    }

    try {
      if ( has_filter( Filters::BUCKAROO_FILTER_BUCKAROO_PARAMS ) ) {
        $buckaroo_params = apply_filters( Filters::BUCKAROO_FILTER_BUCKAROO_PARAMS, $params, $buckaroo_params );
      }

      do_action( Actions::BUCKAROO_RESPONSE_HANDLER, $params, $buckaroo_params );

      $redirect_url = $this->build_redirect_url( $params, $buckaroo_params );
      \wp_safe_redirect( $redirect_url );
    } catch ( \Exception $e ) {
      return $this->rest_response_handler_unknown_error( [ 'error' => $e->getMessage() ] );
    }

    return \rest_ensure_response(
      [
        'code' => 200,
        'data' => [
          'message' => esc_html__( 'Something went wrong, you should have been redirected.' ),
        ],
      ]
    );
  }

  /**
   * Builds the final redirect URL depending on response
   *
   * @param array $params          GET params passed from original Buckaroo route.
   * @param array $buckaroo_params POST params received from Buckaroo (indicating the payment status).
   * @return string
   */
  public function build_redirect_url( array $params, array $buckaroo_params ): string {

    try {
      $buckaroo_response = Response_Factory::build( $buckaroo_params );

      // Get the correct redirect URL (expects them to be urlencoded).
      switch ( $buckaroo_response->get_status() ) {
        case $buckaroo_response::STATUS_CODE_SUCCESS:
          $redirect_url = isset( $params[ self::REDIRECT_URL_PARAM ] ) ? rawurldecode( $params[ self::REDIRECT_URL_PARAM ] ) : '';
              break;
        case $buckaroo_response::STATUS_CODE_ERROR:
          $redirect_url = isset( $params[ self::REDIRECT_URL_ERROR_PARAM ] ) ? rawurldecode( $params[ self::REDIRECT_URL_ERROR_PARAM ] ) : '';
              break;
        case $buckaroo_response::STATUS_CODE_CANCELLED:
          $redirect_url = isset( $params[ self::REDIRECT_URL_CANCEL_PARAM ] ) ? rawurldecode( $params[ self::REDIRECT_URL_CANCEL_PARAM ] ) : '';
              break;
        case $buckaroo_response::STATUS_CODE_REJECT:
          $redirect_url = isset( $params[ self::REDIRECT_URL_REJECT_PARAM ] ) ? rawurldecode( $params[ self::REDIRECT_URL_REJECT_PARAM ] ) : '';
              break;
      }
    } catch ( Invalid_Buckaroo_Response_Exception $e ) {
      $redirect_url = \add_query_arg( 'invalid-buckaroo-response', 1, \home_url() );
    }

    // If the redirect URL wasn't provided, just default to home.
    if ( empty( $redirect_url ) ) {
      $redirect_url = \home_url();
    }

    if ( has_filter( Filters::BUCKAROO_REDIRECT_URL ) ) {
      $redirect_url = apply_filters( Filters::BUCKAROO_REDIRECT_URL, $redirect_url, $params, $buckaroo_params );
    }

    return $redirect_url;
  }

  /**
   * Defines a list of required parameters which must be present in the request or it will error out.
   *
   * @return array
   */
  protected function get_required_params(): array {
    return [
      self::REDIRECT_URL_PARAM,
      self::REDIRECT_URL_CANCEL_PARAM,
      self::REDIRECT_URL_ERROR_PARAM,
      self::REDIRECT_URL_REJECT_PARAM,
      self::STATUS_PARAM,
    ];
  }

  /**
   * Provide the expected salt ($this->get_authorization_salt()) for this route. This
   * should be some secret. For example the secret_key for accessing the 3rd party route this route is
   * handling.
   *
   * If this function returns a non-empty value, it is assumed the route requires authorization.
   *
   * @return string
   */
  protected function get_authorization_salt(): string {
    return \apply_filters( Filters::BUCKAROO, 'secret_key' ) ?? 'invalid-salt';
  }

  /**
   * Override default get_callback_arguments method in order to allow POST requests as well as GET.
   *
   * @return array
   */
  protected function get_callback_arguments() : array {
    return [
      'methods'  => [ static::READABLE, static::CREATABLE ],
      'callback' => [ $this, 'route_callback' ],
    ];
  }
}
