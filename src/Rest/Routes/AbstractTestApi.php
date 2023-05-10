<?php

/**
 * The class register route for public/admin form submiting endpoint
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Troubleshooting\SettingsDebug;
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
	 * Get callback arguments array
	 *
	 * @return array<string, mixed> Either an array of options for the endpoint, or an array of arrays for multiple methods.
	 */
	protected function getCallbackArguments(): array
	{
		return [
			'methods' => $this->getMethods(),
			'callback' => [$this, 'routeCallback'],
			'permission_callback' => [$this, 'permissionCallback'],
		];
	}

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
		// Try catch request.
		try {
			$response = $this->testAction();

			$isDeveloperMode = $this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_DEVELOPER_MODE_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY);

			$additionalOutput = [];

			if ($isDeveloperMode) {
				$additionalOutput['debug'] = $response;
			}

			$code = $response['code'] ?? 400;

			if ($code >= 200 && $code <= 299) {
				return \rest_ensure_response(
					$this->getApiSuccessOutput(
						\esc_html__('The API test was successful.', 'eightshift-forms'),
						$additionalOutput
					)
				);
			}

			return \rest_ensure_response(
				$this->getApiErrorOutput(
					\esc_html__('There seems to be an error with the API test. Please ensure that your credentials are correct.', 'eightshift-forms'),
					$additionalOutput,
				)
			);
		} catch (UnverifiedRequestException $e) {
			// Die if any of the validation fails.
			return \rest_ensure_response(
				$this->getApiErrorOutput(
					$e->getMessage()
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
