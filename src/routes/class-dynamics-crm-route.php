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
  
    // $oauth2 = new OAuth2_Client(
    //   DYNAMICS_CRM_AUTH_TOKEN_URL,
    //   DYNAMICS_CRM_CLIENT_ID,
    //   DYNAMICS_CRM_CLIENT_SECRET,
    //   DYNAMICS_CRM_SCOPE
    // );

    // Now let's try fetching the access token, from transient if set.
    // try {
    //   $token = $oauth2->get_token( self::ACCESS_TOKEN_KEY );
    // } catch ( \Exception $e ) {
    //   return $this->rest_response_handler_unknown_error($e->getMessage());
    // }

    /**
     * WORKING EXAMPLE
     */
    // $odata_service_url = DYNAMICS_CRM_API_URL;
    // $odata_client = new ODataClient( $odata_service_url, function( $request ) use ($token) {

    //   // OAuth Bearer Token Authentication.
    //   $access_token                      = $token;
    //   $request->headers['Authorization'] = 'Bearer ' . $access_token;
    // });

    // Retrieve all entities from the "leads" Entity Set.
    try {
      $leads = $this->dynamics_crm->add_record($entity, $params);
      // $leads = $odata_client->from( $entity )->post($params);
    } catch ( \Exception $e ) {
      return $this->rest_response_handler_unknown_error($e->getMessage());
    }

    return \rest_ensure_response( [
      'code' => 200,
      'data' => $response,
      'leads' => $leads,
      'test' => $this->test_odata(),
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

  /**
   * Tests curl credentials grant.
   *
   * @return void
   */
  protected function test_curl_client_credentials_grant() {
    $body = [
      'grant_type' => 'client_credentials',
      'client_id' => DYNAMICS_CRM_CLIENT_ID,
      'client_secret' => DYNAMICS_CRM_CLIENT_SECRET,
      'scope' => DYNAMICS_CRM_SCOPE
    ];

    
    $client = new \GuzzleHttp\Client();
    $response = $client->get(DYNAMICS_CRM_AUTH_TOKEN_URL, [
      'form_params' => $body,
    ]);

    if ($response->getStatusCode() !== 200) {
      throw new \Exception('Something went wrong, status code: ' . $response->getStatusCode());
    }

    $json_body = json_decode((string) $response->getBody(), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new \Exception('Invalid JSON in body');
    }

    return $json_body;
  }

  /**
   * Working test example of fetching stuff using OData.
   *
   * @return void
   */
  protected function test_odata() {
    $odataServiceUrl = 'https://services.odata.org/V4/TripPinService';

    $odataClient = new ODataClient( $odataServiceUrl );

    // Retrieve all entities from the "People" Entity Set
    $people = $odataClient->from( 'People' )->get();

    // Or retrieve a specific entity by the Entity ID/Key
    // try {
    // $person = $odataClient->from( 'People' )->find( 'russellwhyte' );
    // echo "Hello, I am $person->FirstName ";
    // } catch ( Exception $e ) {
    // echo $e->getMessage();
    // }

    // // Want to only select a few properties/columns?
    // $people = $odataClient->from( 'People' )->select( 'FirstName', 'LastName' )->get();

    return $people;
  }
}
