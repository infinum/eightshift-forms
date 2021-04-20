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
 * @package EightshiftForms\Rest
 */

declare(strict_types=1);

namespace EightshiftForms\Rest;

use EightshiftForms\Exception\MissingFilterInfoException;
use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\Buckaroo\Exceptions\BuckarooRequestException;

/**
 * Class BuckarooPayByEmailRoute
 */
class BuckarooPayByEmailRoute extends AbstractBuckarooRoute
{

  /**
   * Route slug
   *
   * @var string
   */
	public const ENDPOINT_SLUG = '/buckaroo-pay-by-email';

  /**
   * Field to make the Pay by Email payment recurring
   *
   * @var string
   */
	public const IS_RECURRING_PARAM = 'is-recurring';

  /**
   * Method that returns rest response
   *
   * @param  \WP_REST_Request $request Data got from endpoint url.
   *
   * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
   *                                is already an instance, WP_HTTP_Response, otherwise
   *                                returns a new WP_REST_Response instance.
   */
	public function routeCallback(\WP_REST_Request $request)
	{
		try {
			$params = $this->verifyRequest($request, self::BUCKAROO);
		} catch (UnverifiedRequestException $e) {
			return rest_ensure_response($e->get_data());
		}

		try {
			$params = $this->setRedirectUrls($params);
			$this->setTestIfNeeded($params);

		  // Set some default redirect URL. This should be overriden in the filter.
			$redirectUrl = $this->buckaroo->get_return_url();

			if (has_filter(Filters::BUCKAROO_PAY_BY_EMAIL_OVERRIDE)) {
				$redirectUrl = apply_filters(Filters::BUCKAROO_PAY_BY_EMAIL_OVERRIDE, $redirectUrl);
			}
		} catch (MissingFilterInfoException $e) {
			return $this->restResponseHandler('buckaroo-missing-keys', [ 'message' => $e->getMessage() ]);
		} catch (BuckarooRequestException $e) {
			return $this->restResponseHandler('buckaroo-missing-keys', $e->get_exception_for_rest_response());
		} catch (\Exception $e) {
			return $this->restResponseHandlerUnknownError([ 'error' => $e->getMessage() ]);
		}

		return \rest_ensure_response([
			'code' => 200,
			'message' => esc_html__('Pay by email started', 'eightshift-forms'),
			'data' => [
			  'redirectUrl' => $redirectUrl,
			],
		]);
	}
}
