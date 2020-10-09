<?php
/**
 * Endpoint for fetching data from Dynamics CRM.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/dynamics-crm-fetch-entity
 *
 * @package Eightshift_Forms\Rest
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Rest;

use Eightshift_Forms\Cache\Cache;
use Eightshift_Forms\Core\Filters;
use Eightshift_Forms\Integrations\Dynamics_CRM;
use Eightshift_Libs\Core\Config_Data;
use Eightshift_Forms\Captcha\Basic_Captcha;
use Eightshift_Forms\Exception\Unverified_Request_Exception;
use GuzzleHttp\Exception\ClientException;

/**
 * Class Dynamics_Crm_Fetch_Entity_Route
 */
class Dynamics_Crm_Fetch_Entity_Route extends Base_Route {

  /**
   * This is how long this route's response will be cached.
   *
   * @var int
   */
  const HOW_LONG_TO_CACHE_RESPONSE_IN_SEC = 3600;

  const ENTITY_PARAM = 'dynamics-crm-entity';

  /**
   * Route slug
   *
   * @var string
   */
  const ENDPOINT_SLUG = '/dynamics-crm-fetch-entity';

  /**
   * Construct object
   *
   * @param Config_Data  $config          Config data obj.
   * @param Dynamics_CRM $dynamics_crm    Dynamics CRM object.
   * @param Cache        $transient_cache Cache object.
   */
  public function __construct( Config_Data $config, Dynamics_CRM $dynamics_crm, Cache $transient_cache ) {
    $this->config       = $config;
    $this->dynamics_crm = $dynamics_crm;
    $this->cache        = $transient_cache;
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
      $params = $this->verify_request( $request, Filters::DYNAMICS_CRM );
    } catch ( Unverified_Request_Exception $e ) {
      return rest_ensure_response( $e->get_data() );
    }

    // We don't want to send thee entity to CRM or it will reject our request.
    $entity = $params[ self::ENTITY_PARAM ];
    $params = $this->unset_irrelevant_params( $params );

    // Load the response from cache if possible.
    $cache_key = $this->cache->calculate_cache_key_for_request( self::ENDPOINT_SLUG, $this->get_route_uri(), $params );

    if ( $this->cache->exists( $cache_key ) ) {
      return \rest_ensure_response(
        [
          'code' => 200,
          'data' => json_decode( $this->cache->get( $cache_key ), true ),
        ]
      );
    }

    $this->dynamics_crm->set_oauth_credentials(
      [
        'url'           => apply_filters( Filters::DYNAMICS_CRM, 'auth_token_url' ),
        'client_id'     => apply_filters( Filters::DYNAMICS_CRM, 'client_id' ),
        'client_secret' => apply_filters( Filters::DYNAMICS_CRM, 'client_secret' ),
        'scope'         => apply_filters( Filters::DYNAMICS_CRM, 'scope' ),
        'api_url'       => apply_filters( Filters::DYNAMICS_CRM, 'api_url' ),
      ]
    );

    // Retrieve all entities from the "leads" Entity Set.
    try {
      $response = $this->dynamics_crm->fetch_all_from_entity( $entity, $params );
      $this->cache->save( $cache_key, wp_json_encode( $response ), self::HOW_LONG_TO_CACHE_RESPONSE_IN_SEC );
    } catch ( ClientException $e ) {
      return $this->rest_response_handler_unknown_error( [ 'error' => $e->getResponse()->getBody()->getContents() ] );
    } catch ( \Exception $e ) {
      return $this->rest_response_handler_unknown_error( [ 'error' => $e->getMessage() ] );
    }

    return \rest_ensure_response(
      [
        'code' => 200,
        'data' => json_decode( wp_json_encode( $response ), true ),
      ]
    );
  }

  /**
   * Returns keys of irrelevant params which we don't want to send to CRM (even tho they're in form).
   *
   * @return array
   */
  public function permission_callback(): bool {
    return true;
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
   * Defines a list of required parameters which must be present in the request or it will error out.
   *
   * @return array
   */
  protected function get_required_missing_params(): array {
    return [
      self::ENTITY_PARAM,
    ];
  }
}
