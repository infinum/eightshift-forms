<?php

/**
 * Base route (should be extend) for Buckaroo routes.
 *
 * @package EightshiftForms\Rest
 */

declare(strict_types=1);

namespace EightshiftForms\Rest;

use EightshiftForms\Integrations\Buckaroo\Buckaroo;
use EightshiftForms\Integrations\Authorization\AuthorizationInterface;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Captcha\BasicCaptcha;
use EightshiftForms\Integrations\Authorization\Hmac;

/**
 * Class AbstractBuckarooRoute
 */
abstract class AbstractBuckarooRoute extends BaseRoute implements Filters
{

	/**
	 * Issuer, bank code.
	 *
	 * @var string
	 */
	public const ISSUER_PARAM = 'issuer';

	/**
	 * Param for description of this transaction. Not used in Emandates because Emandates
	 * has it's own field / param for this: (see buckaroo-emandate-route).
	 *
	 * @var string
	 */
	public const PAYMENT_DESCRIPTION_PARAM = 'payment-description';

	/**
	 * Test param, set if you wish to Test Buckaroo implementation.
	 *
	 * @var string
	 */
	public const TEST_PARAM = 'test';

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
	 * Buckaroo integration obj.
	 *
	 * @var Buckaroo
	 */
	protected $buckaroo;

	/**
	 * Buckaroo Response Handler Route obj.
	 *
	 * @var BuckarooResponseHandlerRoute
	 */
	protected $buckarooResponseHandlerRoute;

	/**
	 * Implementation of the Authorization obj.
	 *
	 * @var AuthorizationInterface
	 */
	protected $hmac;

	/**
	 * Basic Captcha object.
	 *
	 * @var BasicCaptcha
	 */
	protected $basicCaptcha;

	/**
	 * Construct object
	 *
	 * @param Buckaroo                     $buckaroo                        Buckaroo integration obj.
	 * @param BuckarooResponseHandlerRoute $buckarooResponseHandlerRoute Response handler route obj.
	 * @param AuthorizationInterface       $hmac                            Authorization object.
	 * @param BasicCaptcha                 $basicCaptcha                   BasicCaptcha object.
	 */
	public function __construct(
		Buckaroo $buckaroo,
		BuckarooResponseHandlerRoute $buckarooResponseHandlerRoute,
		AuthorizationInterface $hmac,
		BasicCaptcha $basicCaptcha
	) {
		$this->buckaroo = $buckaroo;
		$this->buckarooResponseHandlerRoute = $buckarooResponseHandlerRoute;
		$this->hmac = $hmac;
		$this->basicCaptcha = $basicCaptcha;
	}

	/**
	 * We need to define redirect URLs so that Buckaroo redirects the user to our buckaroo-response-handler route
	 * which might run some custom logic and then redirect the user to the actual redirect URL as defined in the form's
	 * options.
	 *
	 * @param array $params Array of WP_REST_Request params.
	 * @return array
	 */
	protected function setRedirectUrls(array $params): array
	{

	  // Now let's define all Buckaroo-recognized statuses for which we need to provide redirect URLs.
		$statuses = [
			BuckarooResponseHandlerRoute::STATUS_SUCCESS,
			BuckarooResponseHandlerRoute::STATUS_CANCELED,
			BuckarooResponseHandlerRoute::STATUS_ERROR,
			BuckarooResponseHandlerRoute::STATUS_REJECT,
		];

	  // Now let's build redirect URLs (to buckaroo-response-handler middleware route) for each status.
		$redirectUrls = [];
		$baseUrl      = \home_url($this->buckarooResponseHandlerRoute->getRouteUri());
		foreach ($statuses as $statusValue) {
			$urlParams = $params;
			$urlParams[BuckarooResponseHandlerRoute::STATUS_PARAM] = $statusValue;

		  // We need to encode all params to ensure they're sent properly.
			$urlParams = $this->urlencodeParams($urlParams);

		  // As the last step, add the authorization hash which verifies that the request was not tampered with.
			$url = \add_query_arg(array_merge(
				$urlParams,
				[Hmac::AUTHORIZATION_KEY => rawurlencode($this->hmac->generateHash($urlParams, $this->generateAuthorizationSaltForResponseHandler()))]
			), $baseUrl);

			$redirectUrls[] = $url;
		}

		$this->buckaroo->setRedirectUrls(...$redirectUrls);

		return $params;
	}

	/**
	 * Set Buckaroo to test mode if test param provided.
	 *
	 * @param array $params Request params.
	 * @return void
	 */
	protected function setTestIfNeeded(array $params): void
	{
		if (isset($params[self::TEST_PARAM]) && filter_var($params[self::TEST_PARAM], FILTER_VALIDATE_BOOL)) {
			$this->buckaroo->setTest();
		}
	}

	/**
	 * Define authorization salt used for request to response handler.
	 *
	 * @return string
	 */
	protected function generateAuthorizationSaltForResponseHandler(): string
	{
		return \apply_filters(self::BUCKAROO, 'secretKey') ?? 'invalid-salt-for-buckaroo-handler';
	}

	/**
	 * Toggle if this route requires nonce verification
	 *
	 * @return bool
	 */
	protected function requiresNonceVerification(): bool
	{
		return true;
	}

	/**
	 * Returns allowed methods for this route.
	 *
	 * @return string|array
	 */
	protected function getMethods()
	{
		return static::CREATABLE;
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
	abstract public function routeCallback(\WP_REST_Request $request);
}
