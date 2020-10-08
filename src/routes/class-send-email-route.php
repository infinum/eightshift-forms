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

  const TO_PARAM                 = 'email_to';
  const SUBJECT_PARAM            = 'email_subject';
  const MESSAGE_PARAM            = 'email_message';
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

    try {
      $params = $this->verify_request( $request );
    } catch ( Unverified_Request_Exception $e ) {
      return rest_ensure_response( $e->get_data() );
    }

    $email_info = $this->build_email_info_from_params( $params );

    $email_info['headers'] = $this->add_default_headers( $email_info['headers'] );

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
   * Adds default email headers so email is interpreted as HTML.
   *
   * @param  string $headers Existing headers.
   * @return string
   */
  protected function add_default_headers( string $headers ) {
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
    return $headers;
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
    return [
      'to' => $params[ self::TO_PARAM ] ?? '',
      'subject' => $this->replace_placeholders_with_content( $params[ self::SUBJECT_PARAM ], $params ),
      'message' => $this->replace_placeholders_with_content( $params[ self::MESSAGE_PARAM ], $params ),
      'headers' => $params[ self::ADDITIONAL_HEADERS_PARAM ] ?? '',
    ];
  }

  /**
   * Replaces all placeholders inside a string with actual content from $params (if possible). If not just
   * leave the placeholder in text.
   *
   * @param  string $haystack String in which to look for placeholders.
   * @param  array  $params   Array of params which should hold content for placeholders.
   * @return string
   */
  protected function replace_placeholders_with_content( string $haystack, array $params ) {
    $content = $haystack;

    $content = preg_replace_callback('/\[\[(?<placeholder_key>.+?)\]\]/', function( $match ) use ( $params ) {
      $output = $match[0];
      if ( isset( $params[ $match['placeholder_key'] ] ) ) {
        $output = $params[ $match['placeholder_key'] ];
      }

      return $output;
    }, $haystack);

    return $content;
  }

  /**
   * Defines a list of required parameters which must be present in the request or it will error out.
   *
   * @return array
   */
  protected function get_required_missing_params(): array {
    return [
      self::TO_PARAM,
      self::SUBJECT_PARAM,
      self::MESSAGE_PARAM,
      self::ADDITIONAL_HEADERS_PARAM,
    ];
  }
}
