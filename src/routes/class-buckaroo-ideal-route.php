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

use Eightshift_Forms\Exception\Missing_Filter_Info_Exception;
use Eightshift_Forms\Exception\Unverified_Request_Exception;
use Eightshift_Forms\Hooks\Filters;
use Eightshift_Forms\Integrations\Buckaroo\Exceptions\Buckaroo_Request_Exception;

/**
 * Class Buckaroo_Ideal_Route
 */
class Buckaroo_Ideal_Route extends Base_Buckaroo_Route {

  /**
   * Route slug
   *
   * @var string
   */
  const ENDPOINT_SLUG = '/buckaroo-ideal';

  /**
   * Name of the required parameter for donation amount.
   *
   * @var string
   */
  const DONATION_AMOUNT_PARAM = 'donation-amount';

  /**
   * Field to make the iDEAL payment recurring
   *
   * @var string
   */
  const IS_RECURRING_PARAM = 'is-recurring';

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
      $params = $this->verify_request( $request, self::BUCKAROO );
    } catch ( Unverified_Request_Exception $e ) {
      return rest_ensure_response( $e->get_data() );
    }

    try {
      $this->set_test_if_needed( $params );
      $params = $this->set_redirect_urls( $params );

      $response = $this->buckaroo->send_payment(
        $params[ self::DONATION_AMOUNT_PARAM ],
        $this->buckaroo->generate_purchase_id( $params ),
        $params[ self::ISSUER_PARAM ] ?? '',
        ! empty( $params[ self::IS_RECURRING_PARAM ] ),
        $params[ self::PAYMENT_DESCRIPTION_PARAM ] ?? ''
      );

    } catch ( Missing_Filter_Info_Exception $e ) {
      return $this->rest_response_handler( 'buckaroo-missing-keys', [ 'message' => $e->getMessage() ] );
    } catch ( Buckaroo_Request_Exception $e ) {
      return $this->rest_response_handler( 'buckaroo-request-exception', $e->get_exception_for_rest_response() );
    } catch ( \Exception $e ) {
      return $this->rest_response_handler_unknown_error( [ 'error' => $e->getMessage() ] );
    }

    return \rest_ensure_response(
      [
        'code' => 200,
        'message' => esc_html__( 'Successfully started ideal payment process', 'eightshift-forms' ),
        'data' => $response,
      ]
    );
  }

  /**
   * Defines a list of required parameters which must be present in the request or it will error out.
   *
   * @return array
   */
  protected function get_required_params(): array {
    return [
      self::DONATION_AMOUNT_PARAM,
    ];
  }

  /**
   * Define name of the filter used for filtering required GET params.
   *
   * @return string
   */
  protected function get_required_params_filter(): string {
    return Filters::REQUIRED_PARAMS_BUCKAROO_IDEAL;
  }
}
