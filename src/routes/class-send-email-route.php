<?php
/**
 * Endpoint for sending an email.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/send-email
 *
 * @package Eightshift_Forms\Rest
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Rest;

use Eightshift_Libs\Core\Config_Data;
use Eightshift_Forms\Captcha\Basic_Captcha;
use Eightshift_Forms\Exception\Unverified_Request_Exception;

/**
 * Class Send_Email_Route
 */
class Send_Email_Route extends Base_Route {

  /**
   * Route slug
   *
   * @var string
   */
  const ENDPOINT_SLUG = '/send-email';

  const TO_PARAM                          = 'email_to';
  const SUBJECT_PARAM                     = 'email_subject';
  const MESSAGE_PARAM                     = 'email_message';
  const ADDITIONAL_HEADERS_PARAM          = 'email_additional_headers';
  const SEND_CONFIRMATION_TO_SENDER_PARAM = 'email_send_copy_to_sender';
  const CONFIRMATION_SUBJECT_PARAM        = 'email_confirmation_subject';
  const CONFIRMATION_MESSAGE_PARAM        = 'email_confirmation_message';
  const EMAIL_PARAM                       = 'email';

  /**
   * Construct object.
   *
   * @param Config_Data   $config        Config data obj.
   * @param Basic_Captcha $basic_captcha Basic captcha object.
   */
  public function __construct( Config_Data $config, Basic_Captcha $basic_captcha ) {
    $this->config        = $config;
    $this->basic_captcha = $basic_captcha;
  }

  /**
   * Method that returns rest response.
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

    // If email was sent (and sending a copy back to sender is enabled) we need to validate this email is correct
    if (
      $this->should_send_email_copy_to_user( $params ) &&
      ! $this->is_email_set_and_valid( $params )
     ) {
      return $this->rest_response_handler( 'invalid-email-error' );
    }

    $email_info            = $this->build_email_info_from_params( $params );
    $email_info['headers'] = $this->add_default_headers( $email_info['headers'] );
    $response              = wp_mail( $email_info['to'], $email_info['subject'], $email_info['message'], $email_info['headers'] );

    // If we need to send copy to sender.
    if ( $this->should_send_email_copy_to_user( $params ) ) {
      $email_confirmation_info = $this->build_email_info_from_params( $params, true );
      $response_confirmation   = wp_mail( $params[ self::EMAIL_PARAM ], $email_confirmation_info['subject'], $email_confirmation_info['message'] );
    }

    if (
      ! $response ||
      ( $this->should_send_email_copy_to_user( $params ) && ! $response_confirmation )
    ) {
      return $this->rest_response_handler( 'send-email-error' );
    }

    return \rest_ensure_response([
      'code' => 200,
      'message' => esc_html__( 'Email sent', 'eightshift-forms' ),
      'data' => [],
    ]);
  }

  /**
   * Adds default email headers so email is interpreted as HTML.
   *
   * @param  string $headers Existing headers.
   * @return string
   */
  protected function add_default_headers( string $headers ): string {
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
    return $headers;
  }

  protected function should_send_email_copy_to_user( array $params ): bool {
    return isset( $params[ self::SEND_CONFIRMATION_TO_SENDER_PARAM ] ) && filter_var( $params[ self::SEND_CONFIRMATION_TO_SENDER_PARAM ], FILTER_VALIDATE_BOOL );
  }

  protected function is_email_set_and_valid( array $params ): bool {
    return isset( $params[ self::EMAIL_PARAM ] ) && filter_var( $params[ self::EMAIL_PARAM ], FILTER_VALIDATE_EMAIL );
  }

  /**
   * Takes all parameters received in request and builds all subject / message info needed to send the email.
   * Must return array with the following keys:
   * - to
   * - subject
   * - message
   * - headers
   *
   * @param  array $params              Params received in request.
   * @param  bool  $is_for_confirmation (Optional) If true, we build info for confirmation email sent to user rather than for the admin email.
   * @return array
   */
  protected function build_email_info_from_params( array $params, bool $is_for_confirmation = false ): array {
    $subject_param = $is_for_confirmation ? self::CONFIRMATION_SUBJECT_PARAM : self::SUBJECT_PARAM;
    $message_param = $is_for_confirmation ? self::CONFIRMATION_MESSAGE_PARAM : self::MESSAGE_PARAM;

    return [
      'to' => ! empty( $params[ self::TO_PARAM ] ) ? wp_unslash( sanitize_text_field( strtolower( $params[ self::TO_PARAM ] ) ) ) : '',
      'subject' => $this->replace_placeholders_with_content( $params[ $subject_param ], $params ),
      'message' => $this->replace_placeholders_with_content( $params[ $message_param ], $params ),
      'headers' => $params[ self::ADDITIONAL_HEADERS_PARAM ] ?? '',
    ];
  }

  /**
   * Defines a list of required parameters which must be present in the request or it will error out.
   *
   * @return array
   */
  protected function get_required_params(): array {
    return [
      self::TO_PARAM,
      self::SUBJECT_PARAM,
      self::MESSAGE_PARAM,
    ];
  }
}
