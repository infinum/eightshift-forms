<?php

/**
 * The class register route for public form submiting endpoint - OAuth Callback.
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Captcha\CaptchaInterface;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Rest\Routes\AbstractUtilsBaseRoute;
use Throwable;
use WP_REST_Request;

/**
 * Class Oauth
 */
class Oauth extends AbstractUtilsBaseRoute
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'oauth';

	/**
	 * Instance variable of LabelsInterface data.
	 *
	 * @var LabelsInterface
	 */
	protected $labels;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param LabelsInterface $labels Inject labels methods.
	 */
	public function __construct(
		LabelsInterface $labels,
	) {
		$this->labels = $labels;
	}

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return self::ROUTE_SLUG;
	}

	/**
	 * Returns allowed methods for this route.
	 *
	 * @return string
	 */
	protected function getMethods(): string
	{
		return static::READABLE;
	}

	/**
	 * Method that returns rest response
	 *
	 * @param WP_REST_Request $request Data got from endpoint url.
	 *
	 * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function routeCallback(WP_REST_Request $request)
	{
		$debug = [
			'request' => $request,
		];

		dump($request);

		// Bailout if troubleshooting skip captcha is on.
		if (UtilsDeveloperHelper::isDeveloperSkipCaptchaActive()) {
			// return \rest_ensure_response(
			// 	UtilsApiHelper::getApiSuccessPublicOutput(
			// 		\esc_html__('Form captcha skipped due to troubleshooting config set in settings.', 'eightshift-forms'),
			// 		[],
			// 		$debug
			// 	)
			// );
		}

		try {
			return 'ivan';
			// $params = $this->prepareSimpleApiParams($request);

			// $token = $params['token'];
			// $action = $params['action'];
			// $isEnterprise = $params['isEnterprise'];

			// return \rest_ensure_response(
			// 	$this->captcha->check($token, $action, $isEnterprise === 'true')
			// );
		} catch (Throwable $t) {
			return $t;
			// return \rest_ensure_response(
			// 	UtilsApiHelper::getApiErrorPublicOutput(
			// 		$this->labels->getLabel('captchaBadRequest'),
			// 		[],
			// 		\array_merge(
			// 			$debug,
			// 			[
			// 				'exeption' => $t,
			// 			]
			// 		)
			// 	)
			// );
		}
	}
}
