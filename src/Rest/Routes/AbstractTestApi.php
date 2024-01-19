<?php

/**
 * The class register route for public/admin form submiting endpoint
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Rest\Routes\AbstractUtilsBaseRoute;
use WP_REST_Request;

/**
 * Class AbstractTestApi
 */
abstract class AbstractTestApi extends AbstractUtilsBaseRoute
{
	/**
	 * Dynamic name route prefix for test api.
	 *
	 * @var string
	 */
	public const ROUTE_PREFIX_TEST_API = 'test-api';

	/**
	 * Method that returns rest response
	 *
	 * @param WP_REST_Request $request Data got from endpoint url.
	 *
	 * @throws UnverifiedRequestException Wrong config error.
	 *
	 * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function routeCallback(WP_REST_Request $request)
	{
		$premission = $this->checkUserPermission();
		if ($premission) {
			return \rest_ensure_response($premission);
		}

		$debug = [
			'request' => $request,
		];

		// Try catch request.
		try {
			$response = $this->testAction();

			$additionalOutput = [];

			$debug = \array_merge(
				$debug,
				[
					'response' => $response,
				]
			);

			$code = $response['code'] ?? UtilsConfig::API_RESPONSE_CODE_ERROR;

			if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
				return \rest_ensure_response(
					UtilsApiHelper::getApiSuccessPublicOutput(
						\esc_html__('The API test was successful.', 'eightshift-forms'),
						$additionalOutput,
						$debug
					)
				);
			}

			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					\esc_html__('There seems to be an error with the API test. Please ensure that your credentials are correct.', 'eightshift-forms'),
					$additionalOutput,
					$debug
				)
			);
		} catch (UnverifiedRequestException $e) {
			// Die if any of the validation fails.
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					$e->getMessage(),
					[],
					\array_merge(
						$debug,
						[
							'exception' => $e,
						]
					)
				)
			);
		}
	}

	/**
	 * Implement test action.
	 *
	 * @return mixed
	 */
	abstract protected function testAction();
}
