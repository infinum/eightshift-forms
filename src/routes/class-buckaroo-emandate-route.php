<?php
/**
 * Endpoint for handling Buckaroo integration on form submit.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/buckaroo-emandate
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
 * Class Buckaroo_Emandate_Route
 */
class Buckaroo_Emandate_Route extends Base_Buckaroo_Route {

  /**
   * Route slug
   *
   * @var string
   */
  const ENDPOINT_SLUG = '/buckaroo-emandate';

  /**
   * Description of the emandate param.
   *
   * @var string
   */
  const EMANDATE_DESCRIPTION_PARAM = 'emandate-description';

  /**
   * Name of the required parameter for donation amount.
   *
   * @var string
   */
  const DONATION_AMOUNT_PARAM = 'donation-amount';

  /**
   * Sequencetype param for emandates. 0 = recurring, 1 = one off.
   *
   * @var string
   */
  const SEQUENCE_TYPE_IS_RECURRING_PARAM = 'sequence-type-is-recurring';

  const SEQUENCE_TYPE_RECURRING_VALUE = '0';
  const SEQUENCE_TYPE_ONE_TIME_VALUE = '1';

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

    error_log(print_r($params, true));

    try {
      $params = $this->set_redirect_urls( $params );

      $this->set_test_if_needed( $params );
      $this->buckaroo->set_data_request();

      $this->buckaroo->set_pay_type( 'emandate' );
      $response = $this->buckaroo->create_emandate(
        $this->buckaroo->generate_debtor_reference( $params ),
        ! empty( $params[ self::SEQUENCE_TYPE_IS_RECURRING_PARAM ] ) ? self::SEQUENCE_TYPE_RECURRING_VALUE : self::SEQUENCE_TYPE_ONE_TIME_VALUE,
        $this->buckaroo->generate_purchase_id( $params ),
        'nl',
        $params[ self::ISSUER_PARAM ] ?? '',
        $params[ self::EMANDATE_DESCRIPTION_PARAM ] ?? '',
        $params[ self::DONATION_AMOUNT_PARAM ] ?? null
      );

    } catch ( Missing_Filter_Info_Exception $e ) {
      return $this->rest_response_handler( 'buckaroo-missing-keys', [ 'message' => $e->getMessage() ] );
    } catch ( Buckaroo_Request_Exception $e ) {
      return $this->rest_response_handler( 'buckaroo-missing-keys', $e->get_exception_for_rest_response() );
    } catch ( \Exception $e ) {
      return $this->rest_response_handler_unknown_error( [ 'error' => $e->getMessage() ] );
    }

    return \rest_ensure_response(
      [
        'code' => 200,
        'message' => esc_html__( 'Successfully started emandate creation process', 'eightshift-forms' ),
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
      self::EMANDATE_DESCRIPTION_PARAM,
    ];
  }

  /**
   * Define name of the filter used for filtering required GET params.
   *
   * @return string
   */
  protected function get_required_params_filter(): string {
    return Filters::REQUIRED_PARAMS_BUCKAROO_EMANDATE;
  }
}
