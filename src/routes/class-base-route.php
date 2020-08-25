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

use SaintSystems\OData\ODataClient;

/**
 * Class Dynamics_Crm_Route
 */
abstract class Base_Route extends Libs_Base_Route implements Callable_Route {

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
    return array(
      'methods'  => static::READABLE,
      'callback' => array( $this, 'route_callback' ),
    );
  }

  /**
   * Response handler for unknown errors
   *
   * @param  array $data (Optional) data to output.
   * @return mixed
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
   * @return \WP_Error|array \WP_Error instance with error message and status or array.
   */
  protected function rest_response_handler( string $response_key, array $data = array() ) {
    return \rest_ensure_response( $this->defined_responses( $response_key, $data ) );
  }

  /**
   * Define a list of responses for this route.
   *
   * @param  string $response_key Which response to get.
   * @param  array  $data         (Optional) Data to pass to response handler.
   * @return array
   */
  abstract protected function defined_responses( string $response_key, array $data = array() ): array;

  /**
   * Method that returns rest response
   *
   * @param  \WP_REST_Request $request Data got from enpoint url.
   *
   * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
   *                                is already an instance, WP_HTTP_Response, otherwise
   *                                returns a new WP_REST_Response instance.
   */
  abstract public function route_callback( \WP_REST_Request $request );
}
