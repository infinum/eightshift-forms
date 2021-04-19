<?php
/**
 * Endpoint for fetching segments for a list from Mailerlite.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/mailerlite-fetch-groups
 *
 * @package Eightshift_Forms\Rest
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Rest;

use EightshiftForms\Hooks\Filters;
use Eightshift_Libs\Core\Config_Data;
use Eightshift_Forms\Captcha\Basic_Captcha;
use Eightshift_Forms\Exception\Unverified_Request_Exception;
use Eightshift_Forms\Integrations\Mailerlite\Mailerlite;

/**
 * Class Mailerlite_Fetch_Groups_Route
 */
class Mailerlite_Fetch_Groups_Route extends Base_Route implements Filters {

  /**
   * Route slug
   *
   * @var string
   */
  const ENDPOINT_SLUG = '/mailerlite-fetch-groups';

  /**
   * Config data obj.
   *
   * @var Config_Data
   */
  protected $config;

  /**
   * Mailerlite object.
   *
   * @var Mailerlite
   */
  protected $mailerlite;

  /**
   * Basic Captcha object.
   *
   * @var Basic_Captcha
   */
  protected $basic_captcha;

  /**
   * Construct object
   *
   * @param Config_Data   $config        Config data obj.
   * @param Mailerlite    $mailerlite    Mailerlite object.
   * @param Basic_Captcha $basic_captcha Basic_Captcha object.
   */
  public function __construct( Config_Data $config, Mailerlite $mailerlite, Basic_Captcha $basic_captcha ) {
    $this->config        = $config;
    $this->mailerlite    = $mailerlite;
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
      $params = $this->verify_request( $request );
    } catch ( Unverified_Request_Exception $e ) {
      return rest_ensure_response( $e->get_data() );
    }

    // Retrieve all entities from the "leads" Entity Set.
    try {
      $response = $this->mailerlite->get_all_groups();
    } catch ( \Exception $e ) {
      return \rest_ensure_response([
        'code' => $e->getCode(),
        'message' => esc_html__( 'error', 'eightshift-forms' ),
        'data' => [
          'error' => $e->getMessage(),
        ],
      ]);
    }

    return \rest_ensure_response(
      [
        'code' => 200,
        'data' => $response,
        'message' => esc_html__( 'success', 'eightshift-forms' ),
      ]
    );
  }
}
