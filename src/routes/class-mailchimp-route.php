<?php
/**
 * Endpoint for adding / updating contacts in Mailchimp.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/mailchimp
 *
 * @package Eightshift_Forms\Rest
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Rest;

use EightshiftForms\Hooks\Filters;
use Eightshift_Libs\Core\Config_Data;
use Eightshift_Forms\Captcha\Basic_Captcha;
use Eightshift_Forms\Exception\Missing_Filter_Info_Exception;
use Eightshift_Forms\Exception\Unverified_Request_Exception;
use Eightshift_Forms\Integrations\Mailchimp\Mailchimp;
use GuzzleHttp\Exception\ClientException;

/**
 * Class Mailchimp_Route
 */
class Mailchimp_Route extends Base_Route implements Filters {

  /**
   * Route slug
   *
   * @var string
   */
  const ENDPOINT_SLUG = '/mailchimp';

  /**
   * Parameter for email.
   *
   * @var string
   */
  const EMAIL_PARAM = 'email';

  /**
   * Parameter for list ID.
   *
   * @var string
   */
  const LIST_ID_PARAM = 'list-id';

  /**
   * Parameter for member tag.
   *
   * @var string
   */
  const TAGS_PARAM = 'tags';

  /**
   * Parameter for toggle if we modify Mailchimp user data if they already exist.
   *
   * @var string
   */
  const ADD_EXISTING_MEMBERS_PARAM = 'add-existing-members';

  /**
   * Error if user exists
   *
   * @var string
   */
  const ERROR_USER_EXISTS = 'Member Exists';

  /**
   * Config data obj.
   *
   * @var Config_Data
   */
  protected $config;
  /**
   * Mailchimp object.
   *
   * @var Mailchimp
   */
  protected $mailchimp;

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
   * @param Mailchimp     $mailchimp     Mailchimp object.
   * @param Basic_Captcha $basic_captcha Basic_Captcha object.
   */
  public function __construct( Config_Data $config, Mailchimp $mailchimp, Basic_Captcha $basic_captcha ) {
    $this->config        = $config;
    $this->mailchimp     = $mailchimp;
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

    $list_id                     = $params[ self::LIST_ID_PARAM ] ?? '';
    $email                       = ! empty( $params[ self::EMAIL_PARAM ] ) ? strtolower( $params[ self::EMAIL_PARAM ] ) : '';
    $tags                        = $params[ self::TAGS_PARAM ] ?? [];
    $should_add_existing_members = isset( $params[ self::ADD_EXISTING_MEMBERS_PARAM ] ) ? filter_var( $params[ self::ADD_EXISTING_MEMBERS_PARAM ], FILTER_VALIDATE_BOOL ) : false;
    $merge_field_params          = $this->unset_irrelevant_params( $params );
    $response                    = [];

    // Make sure we have the list ID.
    if ( empty( $list_id ) ) {
      return $this->rest_response_handler( 'mailchimp-missing-list-id' );
    }

    // Make sure we have an email.
    if ( empty( $email ) ) {
      return $this->rest_response_handler( 'mailchimp-missing-email' );
    }

    // Retrieve all entities from the "leads" Entity Set.
    try {
      if ( $should_add_existing_members ) {
        $response['add'] = $this->mailchimp->add_or_update_member( $list_id, $email, $merge_field_params );
      } else {
        $response['add'] = $this->mailchimp->add_member( $list_id, $email, $merge_field_params );
      }

      if ( ! empty( $tags ) ) {
        $response['tags'] = $this->mailchimp->add_member_tags( $list_id, $email, $tags );
      }
    } catch ( ClientException $e ) {
      $decoded_exception = ! empty( $e->getResponse() ) ? json_decode( $e->getResponse()->getBody()->getContents(), true ) : [];

      if ( ! $should_add_existing_members && isset( $decoded_exception['title'] ) && $decoded_exception['title'] === self::ERROR_USER_EXISTS ) {
        $msg_user_exists = \esc_html__( 'User already exists', 'eightshift-forms' );
        $response['add'] = $msg_user_exists;
        $message         = $msg_user_exists;

        // We need to do the "adding tags" call as well (if needed) as the exception in the "add_member" method
        // has stopped execution.
        if ( ! empty( $tags ) ) {
          $response['tags'] = $this->mailchimp->add_member_tags( $list_id, $email, $tags );
        }
      } else {
        return $this->rest_response_handler_unknown_error( [ 'error' => $e->getMessage() ] );
      }
    } catch ( Missing_Filter_Info_Exception $e ) {
      return $this->rest_response_handler( 'mailchimp-missing-keys', [ 'message' => $e->getMessage() ] );
    } catch ( \Exception $e ) {
      return $this->rest_response_handler_unknown_error( [ 'error' => $e->getMessage() ] );
    }

    return \rest_ensure_response(
      [
        'code' => 200,
        'data' => $response,
        'message' => ! empty( $message ) ? $message : \esc_html__( 'Successfully added ', 'eightshift-forms' ),
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
      self::LIST_ID_PARAM,
    ];
  }

  /**
   * Returns keys of irrelevant params which we don't want to send to CRM (even tho they're in form).
   *
   * @return array
   */
  protected function get_irrelevant_params(): array {
    return [
      self::TAGS_PARAM,
      Basic_Captcha::FIRST_NUMBER_KEY,
      Basic_Captcha::SECOND_NUMBER_KEY,
      Basic_Captcha::RESULT_KEY,
      'privacy',
      'privacy-policy',
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
