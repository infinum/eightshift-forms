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
use Eightshift_Forms\Exception\Unverified_Request_Exception;

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

    try {
      $params = $this->verify_request( $request, Filters::BUCKAROO );
    } catch ( Unverified_Request_Exception $e ) {
      return rest_ensure_response( $e->get_data() );
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
      return $this->rest_response_handler( 'buckaroo-missing-keys', [ 'message' => $e->getMessage() ] );
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
