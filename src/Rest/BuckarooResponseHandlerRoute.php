<?php

/**
 * Endpoint for handling Buckaroo integration on form submit.
 *
 * Example call:
 * /wp-json/eightshift-forms/v1/buckaroo-response-handler
 *
 * @package EightshiftForms\Rest
 */

declare(strict_types=1);

namespace EightshiftForms\Rest;

use EightshiftForms\Integrations\Buckaroo\Exceptions\InvalidBuckarooResponseException;
use EightshiftForms\Integrations\Buckaroo\ResponseFactory;
use EightshiftForms\Hooks\Actions;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Integrations\Buckaroo\Buckaroo;
use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Integrations\Authorization\AuthorizationInterface;

/**
 * Class BuckarooResponseHandlerRoute
 */
class BuckarooResponseHandlerRoute extends BaseRoute implements Actions, Filters
{

  /**
   * Route slug
   *
   * @var string
   */
	public const ENDPOINT_SLUG = '/buckaroo-response-handler';

  /**
   * Name of the required parameter for redirect URLs.
   *
   * @var string
   */
	public const REDIRECT_URLS_PARAM = 'redirect-urls';

  /**
   * Name of the required parameter for status of Buckaroo transaction.
   *
   * @var string
   */
	public const STATUS_PARAM = 'status';

  /**
   * Value of the status param.
   *
   * @var string
   */
	public const STATUS_SUCCESS = 'success';

  /**
   * Value of the status param.
   *
   * @var string
   */
	public const STATUS_CANCELED = 'canceled';

  /**
   * Value of the status param for error.
   *
   * @var string
   */
	public const STATUS_ERROR = 'error';

  /**
   * Value of the status param for reject.
   *
   * @var string
   */
	public const STATUS_REJECT = 'reject';

  /**
   * Name of the required parameter for redirect url.
   *
   * @var string
   */
	public const REDIRECT_URL_PARAM = 'redirect-url';

  /**
   * Name of the required parameter for redirect url cancel.
   *
   * @var string
   */
	public const REDIRECT_URL_CANCEL_PARAM = 'redirect-url-cancel';

  /**
   * Name of the required parameter for redirect url error.
   *
   * @var string
   */
	public const REDIRECT_URL_ERROR_PARAM = 'redirect-url-error';

  /**
   * Name of the required parameter for redirect url reject.
   *
   * @var string
   */
	public const REDIRECT_URL_REJECT_PARAM = 'redirect-url-reject';

  /**
   * Name of the required parameter (provided by Buckaroo) indicating response status.
   *
   * @var string
   */
	public const BUCKAROO_RESPONSE_CODE_PARAM = 'BRQ_STATUSCODE';

  /**
   * Buckaroo integration obj.
   *
   * @var Buckaroo
   */
	protected $buckaroo;

  /**
   * Implementation of the Authorization obj.
   *
   * @var AuthorizationInterface
   */
	protected $hmac;

  /**
   * Construct object
   *
   * @param Buckaroo               $buckaroo Buckaroo integration obj.
   * @param AuthorizationInterface $hmac     Authorization object.
   */
	public function __construct(Buckaroo $buckaroo, AuthorizationInterface $hmac)
	{
		$this->buckaroo = $buckaroo;
		$this->hmac     = $hmac;
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
	public function routeCallback(\WP_REST_Request $request)
	{

		try {
			$params          = $this->verifyRequest($request, Filters::BUCKAROO);
			$buckarooParams = $request->get_body_params();
		} catch (UnverifiedRequestException $e) {
			return rest_ensure_response($e->getData());
		}

		try {
			if (has_filter(Filters::BUCKAROO_FILTER_BUCKAROO_PARAMS)) {
				$buckarooParams = apply_filters(Filters::BUCKAROO_FILTER_BUCKAROO_PARAMS, $params, $buckarooParams);
			}

			do_action(Actions::BUCKAROO_RESPONSE_HANDLER, $params, $buckarooParams);

			$redirectUrl = $this->buildRedirectUrl($params, $buckarooParams);
			\wp_safe_redirect($redirectUrl);
		} catch (\Exception $e) {
			return $this->restResponseHandlerUnknownError(['error' => $e->getMessage()]);
		}

		return \rest_ensure_response([
			'code' => 200,
			'data' => [
			  'message' => esc_html__('Something went wrong, you should have been redirected.'),
			],
		]);
	}

  /**
   * Builds the final redirect URL depending on response
   *
   * @param array $params          GET params passed from original Buckaroo route.
   * @param array $buckarooParams POST params received from Buckaroo (indicating the payment status).
   * @return string
   */
	public function buildRedirectUrl(array $params, array $buckarooParams): string
	{

		try {
			$buckarooResponse = ResponseFactory::build($buckarooParams);

		  // Get the correct redirect URL (expects them to be urlencoded).
			switch ($buckarooResponse->getStatus()) {
				case $buckarooResponse::STATUS_CODE_SUCCESS:
					$redirectUrl = isset($params[self::REDIRECT_URL_PARAM]) ? rawurldecode($params[self::REDIRECT_URL_PARAM]) : '';
					break;
				case $buckarooResponse::STATUS_CODE_ERROR:
					$redirectUrl = isset($params[self::REDIRECT_URL_ERROR_PARAM]) ? rawurldecode($params[self::REDIRECT_URL_ERROR_PARAM]) : '';
					break;
				case $buckarooResponse::STATUS_CODE_CANCELLED:
					$redirectUrl = isset($params[self::REDIRECT_URL_CANCEL_PARAM]) ? rawurldecode($params[self::REDIRECT_URL_CANCEL_PARAM]) : '';
					break;
				case $buckarooResponse::STATUS_CODE_REJECT:
					$redirectUrl = isset($params[self::REDIRECT_URL_REJECT_PARAM]) ? rawurldecode($params[self::REDIRECT_URL_REJECT_PARAM]) : '';
					break;
			}
		} catch (InvalidBuckarooResponseException $e) {
			$redirectUrl = \add_query_arg('invalid-buckaroo-response', 1, \home_url());
		}

	  // If the redirect URL wasn't provided, just default to home.
		if (empty($redirectUrl)) {
			$redirectUrl = \home_url();
		}

		if (has_filter(Filters::BUCKAROO_REDIRECT_URL)) {
			$redirectUrl = apply_filters(Filters::BUCKAROO_REDIRECT_URL, $redirectUrl, $params, $buckarooParams);
		}

		return $redirectUrl;
	}

  /**
   * Defines a list of required parameters which must be present in the request or it will error out.
   *
   * @return array
   */
	protected function getRequiredParams(): array
	{
		return [
			self::REDIRECT_URL_PARAM,
			self::REDIRECT_URL_CANCEL_PARAM,
			self::REDIRECT_URL_ERROR_PARAM,
			self::REDIRECT_URL_REJECT_PARAM,
			self::STATUS_PARAM,
		];
	}

  /**
   * Provide the expected salt ($this->getAuthorizationSalt()) for this route. This
   * should be some secret. For example the secretKey for accessing the 3rd party route this route is
   * handling.
   *
   * If this function returns a non-empty value, it is assumed the route requires authorization.
   *
   * @return string
   */
	protected function getAuthorizationSalt(): string
	{
		return \apply_filters(Filters::BUCKAROO, 'secretKey') ?? 'invalid-salt';
	}

  /**
   * Returns allowed methods for this route.
   *
   * @return string|array
   */
	protected function getMethods()
	{
		return [static::READABLE, static::CREATABLE];
	}
}
