<?php
/**
 * Endpoint for handling Buckaroo integration on form submit when type is pay-by-email.
 *
 * IMPORTANT - Currently this doesn't use Buckaroo's integration (because we didn't need this) but
 * it allows us to have a form that behaves like other Buckaroo forms (for purposes of logging things).
 * It doesn't do anything by default unless you provide the custom filter
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/buckaroo-pay-by-email
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
 * Class Buckaroo_Pay_By_Email_Route
 */
class Buckaroo_Pay_By_Email_Route extends Base_Buckaroo_Route {

  /**
   * Route slug
   *
   * @var string
   */
  const ENDPOINT_SLUG = '/buckaroo-pay-by-email';

  /**
   * Field to make the Pay by Email payment recurring
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
      $params = $this->set_redirect_urls( $params );
      $this->set_test_if_needed( $params );

      // Set some default redirect URL. This should be overriden in the filter.
      $redirect_url = $this->buckaroo->get_return_url();

      if ( has_filter( Filters::BUCKAROO_PAY_BY_EMAIL_OVERRIDE ) ) {
        $redirect_url = apply_filters( Filters::BUCKAROO_PAY_BY_EMAIL_OVERRIDE, $redirect_url );
      }

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
        'message' => esc_html__( 'Pay by email started', 'eightshift-forms' ),
        'data' => [
          'redirectUrl' => $redirect_url,
        ],
      ]
    );
  }
}
