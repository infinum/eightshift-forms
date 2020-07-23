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

use SaintSystems\OData\ODataClient;
use Eightshift_Forms\Core\Filters;
use Eightshift_Forms\Integrations\Dynamics_CRM;
use Eightshift_Libs\Core\Config_Data;

/**
 * Class Dynamics_Crm_Route
 */
class Dynamics_Crm_Route extends Base_Route {
  
  const ACCESS_TOKEN_KEY = 'dynamics-crm-access-token';

  const ENTITY_PARAM = 'dynamics-crm-entity';

  /**
   * Route slug
   *
   * @var string
   */
  const ENDPOINT_SLUG = '/dynamics-crm';

  public function __construct(Config_Data $config, Dynamics_CRM $dynamics_crm) {
    $this->config = $config;
    $this->dynamics_crm = $dynamics_crm;
  }

  /**
   * Method that returns rest response
   *
   * @param  \WP_REST_Request $request Data got from enpoint url.
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

    if ( !isset( $params[self::ENTITY_PARAM])) {
      return $this->rest_response_handler( 'missing-entity-key' );
    }
  
    // We don't want to send thee entity to CRM or it will reject our request.
    $entity = $params[self::ENTITY_PARAM];
    unset($params[self::ENTITY_PARAM]);

    $this->dynamics_crm->set_oauth_credentials([
      'url' => apply_filters( Filters::DYNAMICS_CRM, 'auth_token_url' ),
      'client_id' => apply_filters( Filters::DYNAMICS_CRM, 'client_id' ),
      'client_secret' => apply_filters( Filters::DYNAMICS_CRM, 'client_secret' ),
      'scope' => apply_filters( Filters::DYNAMICS_CRM, 'scope' ),
      'api_url' => apply_filters( Filters::DYNAMICS_CRM, 'api_url' ),
    ]);

    // Retrieve all entities from the "leads" Entity Set.
    try {
      $response = $this->dynamics_crm->add_record($entity, $params);
    } catch ( \Exception $e ) {
      return $this->rest_response_handler_unknown_error($e->getMessage());
    }

    return \rest_ensure_response( [
      'code' => 200,
      'data' => $response,
    ] );
  }

  /**
   * Define a list of responses for this route.
   *
   * @return array
   */
  protected function defined_responses(string $response_key, array $data = []): array {
    $responses = [
      'dynamics-crm-integration-not-used' => [
        'code' => 400,
        'message' => sprintf( esc_html__( 'Dynamics CRM integration is not used, please add a %s filter returning all necessary info.', 'eightshift-forms' ), Filters::DYNAMICS_CRM ),
        'data' => $data,
      ],
      'missing-entity-key' => [
        'code' => 400,
        'message' => sprintf( esc_html__( 'Missing %s key in request', 'eightshift-forms' ), self::ENTITY_PARAM ),
        'data' => $data,
      ],
    ];

    return $responses[$response_key];
  }
}
