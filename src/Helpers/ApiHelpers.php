<?php

/**
 * Class that holds all api helpers used in classes.
 *
 * @package EightshiftForms\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

use EightshiftForms\Config\Config;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

/**
 * ApiHelpers class.
 */
final class ApiHelpers
{
	/**
	 * Return API response array details.
	 *
	 * @param array<mixed> $response Response got from the API.
	 *
	 * @return array<string, mixed>
	 */
	/**
	 * Return API response array details.
	 *
	 * @param string $integration Integration name from settings.
	 * @param mixed $response API full response.
	 * @param string $url Url of the request.
	 * @param array<mixed> $params All params prepared for API.
	 * @param array<mixed> $files All files prepared for API.
	 * @param string $itemId List Id used for API (questions, form id, list id, item id).
	 * @param string $formId Internal form ID.
	 * @param boolean $isDisabled If integration is disabled.
	 * @param boolean $isCurl Used for some changed if native cURL is used.
	 *
	 * @return array<string, mixed>
	 */
	public static function getIntegrationApiResponseDetails(
		string $integration,
		$response,
		string $url,
		array $params = [],
		array $files = [],
		string $itemId = '',
		string $formId = '',
		bool $isDisabled = false,
		bool $isCurl = false
	): array {

		// Do regular stuff if this is not and WP_Error.
		if (!\is_wp_error($response)) {
			if ($isCurl) {
				$code = $response['status'] ?? Config::API_RESPONSE_CODE_SUCCESS;
				$body = $response;
			} else {
				$code = $response['response']['code'] ?? Config::API_RESPONSE_CODE_SUCCESS;
				$body = $response['body'] ?? '';

				if (Helpers::isJson($body)) {
					$body = \json_decode($body, true) ?? [];
				}
			}
		} else {
			// Mock response for WP_Error.
			$code = Config::API_RESPONSE_CODE_ERROR;
			$body = [
				'error' => $response->get_error_message(),
			];
			$response = [];
		}

		return [
			Config::IARD_TYPE => $integration,
			Config::IARD_PARAMS => $params,
			Config::IARD_FILES => $files,
			Config::IARD_RESPONSE => $response['response'] ?? [],
			Config::IARD_CODE => $code,
			Config::IARD_BODY => !\is_string($body) ? $body : [],
			Config::IARD_URL => $url,
			Config::IARD_ITEM_ID => $itemId,
			Config::IARD_FORM_ID => $formId,
			Config::IARD_IS_DISABLED => $isDisabled,
		];
	}

	/**
	 * Return Integration API error response array - in combination with getIntegrationApiResponseDetails response.
	 *
	 * NOTE: Not for public response on API.
	 *
	 * @param array<string, mixed> $details Details provided by getIntegrationApiResponseDetails method.
	 * @param array<string, mixed> $additional Additional array details to attach to the success output.
	 *
	 * @return array<string, mixed>
	 */
	public static function getIntegrationErrorInternalOutput(array $details, array $additional = []): array
	{
		return \array_merge(
			$details,
			[
				'status' => Config::STATUS_ERROR,
				'message' => $details[Config::IARD_MSG] ?? '',
			],
			$additional
		);
	}

	/**
	 * Return Integration API success response array - in combination with getIntegrationApiResponseDetails response.
	 *
	 * NOTE: Not for public response on API.
	 *
	 * @param array<string, mixed> $details Details provided by getIntegrationApiResponseDetails method.
	 * @param array<string, mixed> $additional Additional array details to attach to the success output.
	 *
	 * @return array<string, mixed>
	 */
	public static function getIntegrationSuccessInternalOutput(array $details, array $additional = []): array
	{
		$type = $details[Config::IARD_TYPE] ?? '';

		return \array_merge(
			$details,
			[
				'status' => Config::STATUS_SUCCESS,
				'message' => "{$type}Success",
			],
			$additional
		);
	}

	/**
	 * Return API error response array.
	 *
	 * @param string $msg Msg for the user.
	 * @param array<string, mixed> $additional Additonal data to attach to response.
	 * @param array<string, mixed> $debug Debug options.
	 *
	 * @return array<string, array<mixed>|int|string>
	 */
	public static function getApiErrorPublicOutput(string $msg, array $additional = [], array $debug = []): array
	{
		$output = [
			'status' => Config::STATUS_ERROR,
			'code' => Config::API_RESPONSE_CODE_ERROR,
			'message' => $msg,
		];

		if ($additional) {
			$output['data'] = $additional;
		}

		if (DeveloperHelpers::isDeveloperModeActive() && $debug) {
			$output['debug'] = $debug;
		}

		return $output;
	}

	/**
	 * Return API success response array - Generic.
	 *
	 * @param string $msg Msg for the user.
	 * @param array<int|string, mixed> $additional Additional data to attach to response.
	 * @param array<string, mixed> $debug Debug options.
	 *
	 * @return array<string, array<mixed>|int|string>
	 */
	public static function getApiSuccessPublicOutput(string $msg, array $additional = [], array $debug = []): array
	{
		$output = [
			'status' => Config::STATUS_SUCCESS,
			'code' => Config::API_RESPONSE_CODE_SUCCESS,
			'message' => $msg,
		];

		if ($additional) {
			$output['data'] = $additional;
		}

		if (DeveloperHelpers::isDeveloperModeActive() && $debug) {
			$output['debug'] = $debug;
		}

		return $output;
	}

	/**
	 * Return API warning response array - Generic.
	 *
	 * @param string $msg Msg for the user.
	 * @param array<int|string, mixed> $additional Additional data to attach to response.
	 * @param array<string, mixed> $debug Debug options.
	 *
	 * @return array<string, array<mixed>|int|string>
	 */
	public static function getApiWarningPublicOutput(string $msg, array $additional = [], array $debug = []): array
	{
		$output = [
			'status' => Config::STATUS_WARNING,
			'code' => Config::API_RESPONSE_CODE_SUCCESS,
			'message' => $msg,
		];

		if ($additional) {
			$output['data'] = $additional;
		}

		if (DeveloperHelpers::isDeveloperModeActive() && $debug) {
			$output['debug'] = $debug;
		}

		return $output;
	}

	/**
	 * Return API error response array for missing permissions.
	 *
	 * @return array<string, mixed>
	 */
	public static function getApiPermissionsErrorPublicOutput(): array
	{
		return self::getApiErrorPublicOutput(
			\__('You don\'t have enough permissions to perform this action!', 'eightshift-forms'),
		);
	}
}
