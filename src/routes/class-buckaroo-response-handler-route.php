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

use Eightshift_Forms\Hooks\Actions;
use Eightshift_Forms\Hooks\Filters;
use Eightshift_Libs\Core\Config_Data;
use Eightshift_Forms\Integrations\Buckaroo\Buckaroo;
use Eightshift_Forms\Exception\Unverified_Request_Exception;
use Eightshift_Forms\Integrations\Authorization\Authorization_Interface;

/**
 * Class Buckaroo_Response_Handler_Route
 */
class Buckaroo_Response_Handler_Route extends Base_Route implements Actions {

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
      if ( has_action( self::BUCKAROO_RESPONSE_HANDLER ) ) {
        do_action( self::BUCKAROO_RESPONSE_HANDLER, $params, $buckaroo_params );
      }

      \wp_safe_redirect( $params[ self::REDIRECT_URL_PARAM ] );
    } catch ( \Exception $e ) {
      return $this->rest_response_handler_unknown_error( [ 'error' => $e->getMessage() ] );
    }

    return \rest_ensure_response(
      [
        'code' => 200,
        'data' => [
          'message' => esc_html__( 'Something went wrong, you should have been redirected. ' ),
        ],
      ]
    );
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
   * Defines a list of required parameters which must be present in the request or it will error out.
   *
   * @return array
   */
  protected function get_required_post_params(): array {
    return [
      self::BUCKAROO_RESPONSE_CODE_PARAM,
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
