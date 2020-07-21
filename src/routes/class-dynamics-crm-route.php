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

/**
 * Class Dynamics_Crm_Route
 */
class Dynamics_Crm_Route extends Base_Route {

  const ENTITY_PARAM = 'dynamics-crm-entity';

  /**
   * Route slug
   *
   * @var string
   */
  const ENDPOINT_SLUG = '/dynamics-crm';

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

    $params = $request->get_query_params();
    $entity = $params[self::ENTITY_PARAM];

    unset($params[self::ENTITY_PARAM]);
    $post_data = $params;
  
    $response = $this->test_curl_client_credentials_grant();

    /**
     * WORKING EXAMPLE
     */
    $odata_service_url = DYNAMICS_CRM_API_URL;
    $odata_client = new ODataClient( $odata_service_url, function( $request ) use ($response) {

      // OAuth Bearer Token Authentication.
      $access_token                      = $response['access_token'];
      $request->headers['Authorization'] = 'Bearer ' . $access_token;
    });

    // Retrieve all entities from the "leads" Entity Set.
    try {
      $leads = $odata_client->from( $entity )->get();
      $leads = $odata_client->from( $entity )->post($post_data);
    } catch ( \Exception $e ) {
      return \rest_ensure_response( [
        'code' => 400,
        'data' => $e->getMessage(),
      ] );
    }

    return \rest_ensure_response( [
      'code' => 200,
      'data' => $response,
      'leads' => $leads,
      'test' => $this->test_odata(),
    ] );
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
