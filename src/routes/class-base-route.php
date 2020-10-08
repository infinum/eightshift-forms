<?php
/**
 * Endpoint for fetching data for highlight card component.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/dynamics-crm
 *
 * @package Eightshift_Forms\Rest
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Rest;

use Eightshift_Libs\Rest\Base_Route as Libs_Base_Route;
use Eightshift_Libs\Rest\Callable_Route;
use Eightshift_Libs\Core\Config_Data;
use Eightshift_Forms\Core\Filters;
use Eightshift_Forms\Exception\Unverified_Request_Exception;

/**
 * Class Dynamics_Crm_Route
 */
abstract class Base_Route extends Libs_Base_Route implements Callable_Route {

  const MISSING_KEY    = 'missing-key';
  const MISSING_KEYS   = 'missing-keys';
  const MISSING_FILTER = 'missing-filter';

  /**
   * Instance variable of project config data.
   *
   * @var object
   */
  protected $config;

  /**
   * Create a new instance that injects classes
   *
   * @param Config_Data $config Inject config which holds data regarding project details.
   */
  public function __construct( Config_Data $config ) {
    $this->config = $config;
  }

  /**
   * Returns the relative route uri.
   *
   * @return string
   */
  public function get_route_uri(): string {
    return '/wp-json/' . $this->get_namespace() . '/' . $this->get_version() . $this->get_route_name();
  }

  /**
   * By default allow public access to route.
   *
   * @return bool
   */
  public function permission_callback(): bool {
    return true;
  }

  /**
   * Method that returns project Route namespace.
   *
   * @return string Project namespace for REST route.
   */
  protected function get_namespace() : string {
    return $this->config->get_project_name();
  }

  /**
   * Method that returns project route version.
   *
   * @return string Route version as a string.
   */
  protected function get_version() : string {
    return $this->config->get_project_routes_version();
  }

  /**
   * Get the base url of the route
   *
   * @return string The base URL for route you are adding.
   */
  protected function get_route_name() : string {
    return static::ENDPOINT_SLUG;
  }

  /**
   * Get callback arguments array
   *
   * @return array Either an array of options for the endpoint, or an array of arrays for multiple methods.
   */
  protected function get_callback_arguments() : array {
    return [
      'methods'  => static::READABLE,
      'callback' => [ $this, 'route_callback' ],
      'permission_callback' => [ $this, 'permission_callback' ],
    ];
  }

  /**
   * Verifies everything is ok with request
   *
   * @param  \WP_REST_Request $request WP_REST_Request object.
   * @param  string           $required_filter (Optional) Filter that needs to exist to verify this request.
   *
   * @throws Unverified_Request_Exception When we should abort the request for some reason.
   *
   * @return array            filtered request params.
   */
  protected function verify_request( \WP_REST_Request $request, string $required_filter = '' ): array {

    if ( ! empty( $required_filter ) && ! has_filter( $required_filter ) ) {
      throw new Unverified_Request_Exception(
        $this->rest_response_handler( 'integration-not-used', [ self::MISSING_FILTER => $required_filter ] )->data
      );
    }

    $params = $request->get_query_params();
    $params = $this->fix_dot_underscore_replacement( $params );

    if ( ! empty( $this->basic_captcha ) && ! $this->basic_captcha->check_captcha_from_request_params( $params ) ) {
      throw new Unverified_Request_Exception( $this->rest_response_handler( 'wrong-captcha' )->data );
    }

    $missing_params = $this->find_required_missing_params( $params );
    if ( ! empty( $missing_params ) ) {
      throw new Unverified_Request_Exception(
        $this->rest_response_handler( 'missing-params', [ self::MISSING_KEY => $missing_params ] )->data
      );
    }

    return $params;
  }

  /**
   * Checks if all required parameters are present in request.
   *
   * @param  array $parameters Array of request parameters.
   * @return array Returns array of missing parameters to pass in response.
   */
  protected function find_required_missing_params( array $parameters ): array {
    $missing_params = [];
    foreach ( $this->get_required_missing_params() as $required_param ) {
      if ( ! isset( $parameters[ $required_param ] ) ) {
        $missing_params[ self::MISSING_KEYS ] [] = $required_param;
      }
    }

    return $missing_params;
  }

  /**
   * Defines a list of required parameters which must be present in the request or it will error out.
   *
   * @return array
   */
  protected function get_required_missing_params(): array {
    return [];
  }

  /**
   * WordPress replaces dots with underscores for some reason. This is undesired behavior when we need to map
   * need record field values to existing lookup fields (we need to use @odata.bind in field's key).
   *
   * Quick and dirty fix is to replace these values back to dots after receiving them.
   *
   * @param array $params Request params.
   * @return array
   */
  protected function fix_dot_underscore_replacement( array $params ): array {
    foreach ( $params as $key => $value ) {
      if ( strpos( $key, '@odata_bind' ) !== false ) {
        $new_key = str_replace( '@odata_bind', '@odata.bind', $key );
        unset( $params[ $key ] );
        $params[ $new_key ] = $value;
      }
    }

    return $params;
  }

  /**
   * Response handler for unknown errors
   *
   * @param  array $data (Optional) data to output.
   * @return \WP_REST_Response|WP_Error|WP_HTTP_Response|mixed
   */
  protected function rest_response_handler_unknown_error( array $data = array() ) {

    return \rest_ensure_response(
      array(
        'code' => 400,
        'message' => esc_html__( 'Unknown error', 'eightshift-forms' ),
        'data' => $data,
      )
    );
  }

  /**
   * Ensure correct response for rest using error handler function.
   *
   * @param  string $response_key Which response to get.
   * @param  array  $data         (Optional) Data to pass to response handler.
   *
   * @return \WP_REST_Response|WP_Error|WP_HTTP_Response|mixed
   */
  protected function rest_response_handler( string $response_key, array $data = array() ) {
    $responses = array_merge( $this->route_responses(), $this->all_responses() );

    $response = $responses[ $response_key ] ?? [
      'code' => 400,
      'message' => esc_html__( 'Undefined response', 'eightshift-forms' ),
    ];

    $response['data'] = $data;
    return \rest_ensure_response( $response );
  }

  /**
   * Define a list of responses for this route.
   *
   * @return array
   */
  protected function route_responses(): array {
    return [];
  }

  /**
   * A list of all responses.
   *
   * @return array
   */
  protected function all_responses(): array {
    return [
      'wrong-captcha' => [
        'code' => 429,
        'message' => esc_html__( 'Wrong captcha answer.', 'eightshift-forms' ),
      ],
      'send-email-error' => [
        'code' => 400,
        'message' => esc_html__( 'Error while sending an email.', 'eightshift-forms' ),
      ],
      'missing-params' => [
        'code' => 400,
        'message' => esc_html__( 'Missing one or more required parameters to process the request.', 'eightshift-forms' ),
      ],
      'integration-not-used' => [
        'code' => 400,
        'message' => sprintf( esc_html__( 'This form integration is not used, please add a filter returning all necessary info.', 'eightshift-forms' ) ),
      ],

      // Buckaroo specific.
      'buckaroo-missing-keys' => [
        'code' => 400,
        'message' => esc_html__( 'Not all Buckaroo keys are set', 'eightshift-forms' ),
      ],
    ];
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
