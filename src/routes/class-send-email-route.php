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

  const TO_PARAM = 'email_to';
  const SUBJECT_PARAM = 'email_subject';
  const MESSAGE_PARAM = 'email_message';
  const ADDITIONAL_HEADERS_PARAM = 'email_additional_headers';

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

    $params = $request->get_query_params();

    if ( ! $this->basic_captcha->check_captcha_from_request_params( $params ) ) {
      return $this->rest_response_handler( 'wrong-captcha' );
    }

    $missing_params = $this->find_required_missing_params( $params );
    if ( ! empty( $missing_params ) ) {
      return $this->rest_response_handler( 'missing-params', $missing_params );
    }

    $email_info = $this->build_email_info_from_params( $params );

    $response = wp_mail( $email_info['to'], $email_info['subject'], $email_info['message'], $email_info['headers'] );

    if ( ! $response ) {
      return $this->rest_response_handler( 'send-email-error' );
    }

    return \rest_ensure_response([
      'code' => 200,
      'message' => esc_html__( 'Email sent', 'd66' ),
    ]);
  }

  /**
   * Takes all parameters received in request and builds all subject / message info needed to send the email.
   * Must return array with the following keys:
   * - to
   * - subject
   * - message
   * - headers
   *
   * @param  array $params Params received in request.
   * @return array
   */
  protected function build_email_info_from_params( array $params ): array {
    // $to          = sanitize_text_field( wp_unslash( $_GET['to'] ) );
    // $subject     = sanitize_text_field( wp_unslash( $_GET['subject'] ) );
    // $message     = sanitize_text_field( wp_unslash( $_GET['message'] ) );
    // $attachments = sanitize_text_field( wp_unslash( $_GET['attachments'] ?? '' ) );
    // $headers     = array_map( function( $header ) {
    //   return sanitize_text_field( wp_unslash( $header ) );
    // }, $_GET['headers'] ?? [] );

    return $params
  }

  /**
   * Defines a list of required parameters which must be present in the request or it will error out.
   *
   * @return array
   */
  protected function get_required_missing_params(): array {
    return [
      'to',
      'subject',
      'message',
    ];
  }

  /**
   * Define a list of responses for this route.
   *
   * @return array
   */
  protected function defined_responses( string $response_key, array $data = [] ): array {
    $responses = [
      'wrong-captcha' => [
        'code' => 429,
        'message' => esc_html__( 'Wrong captcha answer.', 'eightshift-forms' ),
        'data' => $data,
      ],
      'send-email-error' => [
        'code' => 400,
        'message' => esc_html__( 'Error while sending an email.', 'eightshift-forms' ),
        'data' => $data,
      ],
      'missing-params' => [
        'code' => 400,
        'message' => esc_html__( 'Missing one or more required parameters to process the request.', 'eightshift-forms' ),
        'data' => $data,
      ],
    ];

    return $responses[ $response_key ];
  }
}
