<?php
/**
 * Endpoint for handling Buckaroo integration on form submit.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/buckaroo
 *
 * @package Eightshift_Forms\Rest
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Rest;

use Eightshift_Forms\Core\Filters;
use Eightshift_Libs\Core\Config_Data;
use Eightshift_Forms\Captcha\Basic_Captcha;
use Eightshift_Forms\Integrations\Buckaroo\Buckaroo;
use Eightshift_Forms\Exception\Missing_Filter_Info_Exception;

/**
 * Class Buckaroo_Ideal_Route
 */
class Buckaroo_Ideal_Route extends Base_Route {

  /**
   * Route slug
   *
   * @var string
   */
  const ENDPOINT_SLUG = '/buckaroo-ideal';

  /**
   * Issuer, bank code.
   */
  const ISSUER_PARAM = 'issuer';

  /**
   * Name of the required parameter for donation amount.
   *
   * @var string
   */
  const DONATION_AMOUNT_PARAM = 'donation-amount';

  /**
   * Name of the required parameter for redirect url.
   *
   * @var string
   */
  const REDIRECT_URL_PARAM = 'redirect-url';

  /**
   * Name of the required parameter for redirect url cancel.
   *
   * @var string
   */
  const REDIRECT_URL_CANCEL_PARAM = 'redirect-url-cancel';

  /**
   * Name of the required parameter for redirect url error.
   *
   * @var string
   */
  const REDIRECT_URL_ERROR_PARAM = 'redirect-url-error';

  /**
   * Name of the required parameter for redirect url reject.
   *
   * @var string
   */
  const REDIRECT_URL_REJECT_PARAM = 'redirect-url-reject';

  /**
   * Construct object
   *
   * @param Config_Data   $config        Config data obj.
   * @param Buckaroo      $buckaroo      Buckaroo integration obj.
   * @param Basic_Captcha $basic_captcha Basic_Captcha object.
   */
  public function __construct( Config_Data $config, Buckaroo $buckaroo, Basic_Captcha $basic_captcha ) {
    $this->config        = $config;
    $this->buckaroo      = $buckaroo;
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

    if ( ! has_filter( Filters::BUCKAROO ) ) {
      return $this->rest_response_handler( 'buckaroo-integration-not-used' );
    }

    $params = $request->get_query_params();
    $params = $this->fix_dot_underscore_replacement( $params );

    if ( ! $this->basic_captcha->check_captcha_from_request_params( $params ) ) {
      return $this->rest_response_handler( 'wrong-captcha' );
    }

    $missing_params = $this->find_required_missing_params( $params );
    if ( ! empty( $missing_params ) ) {
      return $this->rest_response_handler( 'missing-params', $missing_params );
    }

    try {
      $this->buckaroo->set_redirect_urls(
        $params[ self::REDIRECT_URL_PARAM ] ?? '',
        $params[ self::REDIRECT_URL_CANCEL_PARAM ] ?? '',
        $params[ self::REDIRECT_URL_ERROR_PARAM ] ?? '',
        $params[ self::REDIRECT_URL_REJECT_PARAM ] ?? ''
      );

      $this->buckaroo->set_test();
      $response = $this->buckaroo->send_payment(
        $params[ self::DONATION_AMOUNT_PARAM ],
        'test invoice 123',
        $params[ self::ISSUER_PARAM ] ?? 'ABNANL2A'
      );
    } catch ( Missing_Filter_Info_Exception $e ) {
      return $this->rest_response_handler( 'buckaroo-missing-keys', [ 'message' => $e->getMessage()] );
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
    return [];
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
   * WordPress replaces dots with underscores for some reason. This is undesired behavior when we need to map
   * need record field values to existing lookup fields (we need to use @odata.bind in field's key).
   *
   * Quick and dirty fix is to replace these values back to dots after receiving them.
   *
   * @param array $params Request params.
   * @return array
   */
  protected function fix_dot_underscore_replacement( array $params ): array {
    foreach ( $params as $key => $value ) {
      if ( strpos( $key, '@odata_bind' ) !== false ) {
        $new_key = str_replace( '@odata_bind', '@odata.bind', $key );
        unset( $params[ $key ] );
        $params[ $new_key ] = $value;
      }
    }

    return $params;
  }

  /**
   * Defines a list of required parameters which must be present in the request or it will error out.
   *
   * @return array
   */
  protected function get_required_missing_params(): array {
    return [
      self::DONATION_AMOUNT_PARAM,
    ];
  }
}
