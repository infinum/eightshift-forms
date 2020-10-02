<?php
/**
 * Endpoint for handling Buckaroo integration on form submit.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/buckaroo
 *
 * @package Eightshift_Forms\Rest
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Rest;

use Eightshift_Forms\Core\Filters;
use Eightshift_Libs\Core\Config_Data;
use Eightshift_Forms\Captcha\Basic_Captcha;

/**
 * Class Buckaroo_Route
 */
class Buckaroo_Route extends Base_Route {

  /**
   * Route slug
   *
   * @var string
   */
  const ENDPOINT_SLUG = '/buckaroo';

  /**
   * Construct object
   *
   * @param Config_Data   $config       Config data obj.
   * @param Basic_Captcha $basic_captcha Basic_Captcha object.
   */
  public function __construct( Config_Data $config, Basic_Captcha $basic_captcha ) {
    $this->config        = $config;
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

    if ( ! has_filter( Filters::DYNAMICS_CRM ) ) {
      return $this->rest_response_handler( 'dynamics-crm-integration-not-used' );
    }

    $params = $request->get_query_params();
    $params = $this->fix_dot_underscore_replacement( $params );

    if ( ! $this->basic_captcha->check_captcha_from_request_params( $params ) ) {
      return $this->rest_response_handler( 'wrong-captcha' );
    }

    $response = 'so far so good';

    return \rest_ensure_response(
      [
        'code' => 200,
        'data' => $response,
      ]
    );
  }

  /**
   * Returns keys of irrelevant params which we don't want to send to CRM (even tho they're in form).
   *
   * @return array
   */
  protected function get_irrelevant_params(): array {
    return [
      self::ENTITY_PARAM,
      Basic_Captcha::FIRST_NUMBER_KEY,
      Basic_Captcha::SECOND_NUMBER_KEY,
      Basic_Captcha::RESULT_KEY,
      'privacy',
      'privacy-policy',
    ];
  }


  /**
   * Removes some params we don't want to send to CRM from request.
   *
   * @param  array $params Params received in request.
   * @return array
   */
  protected function unset_irrelevant_params( array $params ): array {
    $filtered_params   = [];
    $irrelevant_params = array_flip( $this->get_irrelevant_params() );

    foreach ( $params as $key => $param ) {
      if ( ! isset( $irrelevant_params [ $key ] ) ) {
        $filtered_params[ $key ] = $param;
      }
    }

    return $filtered_params;
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
   * Define a list of responses for this route.
   *
   * @param  string $response_key Which key to return.
   * @param  array  $data         Optional data to also return in response.
   * @return array
   */
  protected function defined_responses( string $response_key, array $data = [] ): array {
    $responses = [
      'wrong-captcha' => [
        'code' => 429,
        'message' => esc_html__( 'Wrong captcha answer.', 'eightshift-forms' ),
        'data' => $data,
      ],
      'buckaroo-integration-not-used' => [
        'code' => 400,
        'message' => sprintf( esc_html__( 'Buckaroo is not used, please add a %s filter returning all necessary info.', 'eightshift-forms' ), Filters::BUCKAROO ),
        'data' => $data,
      ],
      'missing-entity-key' => [
        'code' => 400,
        'message' => sprintf( esc_html__( 'Missing %s key in request', 'eightshift-forms' ), self::ENTITY_PARAM ),
        'data' => $data,
      ],
    ];

    return $responses[ $response_key ];
  }
}
