<?php
/**
 * Endpoint for adding / updating contacts in Mailerlite.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/mailerlite
 *
 * @package Eightshift_Forms\Rest
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Rest;

use Eightshift_Forms\Hooks\Filters;
use Eightshift_Libs\Core\Config_Data;
use Eightshift_Forms\Captcha\Basic_Captcha;
use Eightshift_Forms\Exception\Missing_Filter_Info_Exception;
use Eightshift_Forms\Exception\Unverified_Request_Exception;
use Eightshift_Forms\Integrations\Mailerlite\Mailerlite;
use Http\Client\Exception\HttpException;

/**
 * Class Mailerlite_Route
 */
class Mailerlite_Route extends Base_Route implements Filters {

  /**
   * Route slug
   *
   * @var string
   */
  const ENDPOINT_SLUG = '/mailerlite';

  /**
   * Parameter for email.
   *
   * @var string
   */
  const EMAIL_PARAM = 'email';

  /**
   * Parameter for group id.
   *
   * @var string
   */
  const GROUP_ID_PARAM = 'group_id';

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

    $email              = ! empty( $params[ self::EMAIL_PARAM ] ) ? strtolower( $params[ self::EMAIL_PARAM ] ) : '';
    $group_id           = ! empty( $params[ self::GROUP_ID_PARAM ] ) ? (int) $params[ self::GROUP_ID_PARAM ] : 0;
    $merge_field_params = $this->unset_irrelevant_params( $params );
    $response           = '';
    $message            = '';

    // Make sure we have the group ID.
    if ( empty( $group_id ) ) {
      return $this->rest_response_handler( 'mailerlite-missing-group-id' );
    }

    // Make sure we have an email.
    if ( empty( $email ) ) {
      return $this->rest_response_handler( 'mailerlite-missing-email' );
    }

    // Retrieve all entities from the "leads" Entity Set.
    try {
      $response = $this->mailerlite->add_subscriber( $group_id, $email, $merge_field_params );
    } catch ( Missing_Filter_Info_Exception $e ) {
      return $this->rest_response_handler( 'mailerlite-missing-keys', [ 'message' => $e->getMessage() ] );
    } catch ( HttpException $e ) {
      $msg     = $e->getResponse()->getBody()->getContents();
      $message = json_decode( $msg, true )['error']['message'];

      return $this->rest_response_handler( 'mailerlite-blocked-email', [ 'message' => $message ] );
    } catch ( \Exception $e ) {
      return $this->rest_response_handler_unknown_error( [ 'error' => $e->getMessage() ] );
    }

    return \rest_ensure_response(
      [
        'code' => 200,
        'data' => $response,
        'message' => ! empty( $message ) ? $message : \esc_html__( 'Successfully added', 'eightshift-forms' ),
      ]
    );
  }

  /**
   * Defines a list of required parameters which must be present in the request as GET parameters or it will error out.
   *
   * @return array
   */
  protected function get_required_params(): array {
    return [
      self::EMAIL_PARAM,
      self::GROUP_ID_PARAM,
    ];
  }

  /**
   * Returns keys of irrelevant params which we don't want to send to CRM (even tho they're in form).
   *
   * @return array
   */
  protected function get_irrelevant_params(): array {
    return [
      Basic_Captcha::FIRST_NUMBER_KEY,
      Basic_Captcha::SECOND_NUMBER_KEY,
      Basic_Captcha::RESULT_KEY,
    ];
  }

  /**
   * Toggle if this route requires nonce verification
   *
   * @return bool
   */
  protected function requires_nonce_verification(): bool {
    return true;
  }

  /**
   * Returns allowed methods for this route.
   *
   * @return string|array
   */
  protected function get_methods() {
    return static::CREATABLE;
  }
}
