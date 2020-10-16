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

use Eightshift_Forms\Hooks\Filters;
use Eightshift_Libs\Core\Config_Data;
use Eightshift_Forms\Captcha\Basic_Captcha;
use Eightshift_Forms\Exception\Missing_Filter_Info_Exception;
use Eightshift_Forms\Exception\Unverified_Request_Exception;
use Eightshift_Forms\Integrations\Mailchimp\Mailchimp;

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

    $list_id = $params[ self::LIST_ID_PARAM ] ?? '';
    $email   = $params[ self::EMAIL_PARAM ] ?? '';

    // Retrieve all entities from the "leads" Entity Set.
    try {
      $response = $this->mailchimp->add_or_update_member(
        $list_id,
        $email
      );
      $response_tags = $this->mailchimp->update_list_member_tags(
        $list_id,
        $email,
        [
          [
            'name' => 'testtag',
            'status' => 'active'
          ],
        ]
      );

    } catch ( Missing_Filter_Info_Exception $e ) {
      return $this->rest_response_handler( 'mailchimp-missing-keys', [ 'message' => $e->getMessage() ] );
    } catch ( \Exception $e ) {
      return $this->rest_response_handler_unknown_error( [ 'error' => $e->getMessage() ] );
    }

    return \rest_ensure_response(
      [
        'code' => 200,
        'data' => $response,
        'message' => esc_html__( 'success', 'eightshift-forms' ),
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
}
