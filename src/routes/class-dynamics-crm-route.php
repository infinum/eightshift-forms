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

use Eightshift_Forms\Hooks\Filters;
use Eightshift_Forms\Integrations\Dynamics_CRM;
use Eightshift_Libs\Core\Config_Data;
use Eightshift_Forms\Captcha\Basic_Captcha;
use Eightshift_Forms\Exception\Unverified_Request_Exception;

/**
 * Class Dynamics_Crm_Route
 */
class Dynamics_Crm_Route extends Base_Route implements Filters {

  const ENTITY_PARAM = 'dynamics-crm-entity';

  /**
   * Route slug
   *
   * @var string
   */
  const ENDPOINT_SLUG = '/dynamics-crm';

  /**
   * Config data obj.
   *
   * @var Config_Data
   */
  protected $config;

  /**
   * Dynamics CRM object.
   *
   * @var Dynamics_CRM
   */
  protected $dynamics_crm;

  /**
   * Basic Captcha object.
   *
   * @var Basic_Captcha
   */
  protected $basic_captcha;

  /**
   * Construct object
   *
   * @param Config_Data   $config       Config data obj.
   * @param Dynamics_CRM  $dynamics_crm Dynamics CRM object.
   * @param Basic_Captcha $basic_captcha Basic_Captcha object.
   */
  public function __construct( Config_Data $config, Dynamics_CRM $dynamics_crm, Basic_Captcha $basic_captcha ) {
    $this->config        = $config;
    $this->dynamics_crm  = $dynamics_crm;
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

    try {
      $params = $this->verify_request( $request, self::DYNAMICS_CRM );
    } catch ( Unverified_Request_Exception $e ) {
      return rest_ensure_response( $e->get_data() );
    }

    // We don't want to send thee entity to CRM or it will reject our request.
    $entity = $params[ self::ENTITY_PARAM ];
    $params = $this->unset_irrelevant_params( $params );

    $this->dynamics_crm->set_oauth_credentials(
      [
        'url'           => apply_filters( self::DYNAMICS_CRM, 'auth_token_url' ),
        'client_id'     => apply_filters( self::DYNAMICS_CRM, 'client_id' ),
        'client_secret' => apply_filters( self::DYNAMICS_CRM, 'client_secret' ),
        'scope'         => apply_filters( self::DYNAMICS_CRM, 'scope' ),
        'api_url'       => apply_filters( self::DYNAMICS_CRM, 'api_url' ),
      ]
    );

    // Retrieve all entities from the "leads" Entity Set.
    try {
      $response = $this->dynamics_crm->add_record( $entity, $params );
    } catch ( \Exception $e ) {
      return $this->rest_response_handler_unknown_error( [ 'error' => $e->getResponse()->getBody()->getContents() ] );
    }

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
   * Defines a list of required parameters which must be present in the request or it will error out.
   *
   * @return array
   */
  protected function get_required_params(): array {
    return [
      self::ENTITY_PARAM,
    ];
  }
}
