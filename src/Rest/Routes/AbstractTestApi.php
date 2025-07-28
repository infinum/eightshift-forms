<?php

/**
 * The class register route for public/admin form submitting endpoint
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Config\Config;
use WP_REST_Request;

/**
 * Class AbstractTestApi
 */
abstract class AbstractTestApi extends AbstractBaseRoute
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
		$permission = $this->checkUserPermission(Config::CAP_SETTINGS);
		if ($permission) {
			return \rest_ensure_response($permission);
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

			$code = $response['code'] ?? Config::API_RESPONSE_CODE_ERROR;

			if ($code >= Config::API_RESPONSE_CODE_SUCCESS && $code <= Config::API_RESPONSE_CODE_SUCCESS_RANGE) {
				return \rest_ensure_response(
					ApiHelpers::getApiSuccessPublicOutput(
						\esc_html__('The API test was successful.', 'eightshift-forms'),
						$additionalOutput,
						$debug
					)
				);
			}

			return \rest_ensure_response(
				ApiHelpers::getApiErrorPublicOutput(
					\esc_html__('There seems to be an error with the API test. Please ensure that your credentials are correct.', 'eightshift-forms'),
					$additionalOutput,
					$debug
				)
			);
		} catch (UnverifiedRequestException $e) {
			// Die if any of the validation fails.
			return \rest_ensure_response(
				ApiHelpers::getApiErrorPublicOutput(
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
