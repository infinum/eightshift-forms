<?php

/**
 * Trait that holds all api helpers used in classes.
 *
 * @package EightshiftLibs\Rest
 */

declare(strict_types=1);

namespace EightshiftForms\Rest;

use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftForms\Validation\Validator;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

/**
 * ApiHelper trait.
 */
trait ApiHelper
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

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
	 * @param mixed $response API full reponse.
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
	public function getIntegrationApiReponseDetails(
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
				$code = $response['status'] ?? 200;
				$body = $response;
			} else {
				$code = $response['response']['code'] ?? 200;
				$body = $response['body'] ?? '';

				if (Components::isJson($body)) {
					$body = \json_decode($body, true) ?? [];
				}
			}
		} else {
			// Mock response for WP_Error.
			$code = 404;
			$body = [
				'error' => $response->get_error_message(),
			];
			$response = [];
		}

		return [
			'integration' => Components::kebabToCamelCase($integration, '-'),
			'params' => $params,
			'files' => $files,
			'response' => $response['response'] ?? [],
			'code' => $code,
			'body' => !\is_string($body) ? $body : [],
			'url' => $url,
			'itemId' => $itemId,
			'formId' => $formId,
			'isDisabled' => $isDisabled,
		];
	}

	/**
	 * Return Integration API error response array - in combination with getIntegrationApiReponseDetails response.
	 *
	 * @param array<string, mixed> $details Details provided by getIntegrationApiReponseDetails method.
	 * @param string $msg Message to output.
	 * @param array<string, mixed> $additional Additional array details to attach to the success output.
	 *
	 * @return array<string, mixed>
	 */
	public function getIntegrationApiErrorOutput(array $details, string $msg, array $additional = []): array
	{
		unset($details['status']);
		unset($details['message']);

		return \array_merge(
			[
				'status' => AbstractBaseRoute::STATUS_ERROR,
				'message' => $msg,
			],
			$details,
			$additional
		);
	}

	/**
	 * Return Integration API success response array - in combination with getIntegrationApiReponseDetails response.
	 *
	 * @param array<string, mixed> $details Details provided by getIntegrationApiReponseDetails method.
	 * @param array<string, mixed> $additional Additional array details to attach to the success output.
	 *
	 * @return array<string, mixed>
	 */
	public function getIntegrationApiSuccessOutput(array $details, array $additional = []): array
	{
		$integration = $details['integration'] ?? '';

		unset($details['status']);
		unset($details['message']);

		return \array_merge(
			[
				'status' => AbstractBaseRoute::STATUS_SUCCESS,
				'message' => "{$integration}Success",
			],
			$details,
			$additional
		);
	}

	/**
	 * Return Integration API final response array depending on the status - in combination with getIntegrationApiReponseDetails response.
	 *
	 * @param array<string, mixed> $details Details provided by getIntegrationApiReponseDetails method.
	 * @param string $msg Message to output.
	 * @param array<int, string> $additional Additional array details to attach to the success output.
	 *
	 * @return array<string, array<mixed>|int|string>
	 */
	public function getIntegrationApiOutput(array $details, string $msg, array $additional = []): array
	{
		$status = $details['status'] ?? AbstractBaseRoute::STATUS_ERROR;

		$additionalOutput = [];

		$additional = [
			Validator::VALIDATOR_OUTPUT_KEY,
			...$additional,
		];

		foreach ($additional as $value) {
			if (isset($details[$value])) {
				$additionalOutput[$value] = $details[$value];
			}
		}

		if ($status === AbstractBaseRoute::STATUS_SUCCESS) {
			return $this->getApiSuccessOutput(
				$msg,
				$additionalOutput,
				$details
			);
		}

		return $this->getApiErrorOutput(
			$msg,
			$additionalOutput,
			$details
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
	public function getApiErrorOutput(string $msg, array $additional = [], array $debug = []): array
	{
		$output = [
			'status' => AbstractBaseRoute::STATUS_ERROR,
			'code' => 400,
			'message' => $msg,
		];

		if ($additional) {
			$output['data'] = $additional;
		}

		$isDeveloperMode = $this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_DEVELOPER_MODE_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY);

		if ($isDeveloperMode && $debug) {
			$output['debug'] = $debug;
		}

		return $output;
	}

	/**
	 * Return API success response array - Generic.
	 *
	 * @param string $msg Msg for the user.
	 * @param array<int|string, mixed> $additional Additonal data to attach to response.
	 * @param array<string, mixed> $debug Debug options.
	 *
	 * @return array<string, array<mixed>|int|string>
	 */
	public function getApiSuccessOutput(string $msg, array $additional = [], array $debug = []): array
	{
		$output = [
			'status' => AbstractBaseRoute::STATUS_SUCCESS,
			'code' => 200,
			'message' => $msg,
		];

		if ($additional) {
			$output['data'] = $additional;
		}

		$isDeveloperMode = $this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_DEVELOPER_MODE_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY);

		if ($isDeveloperMode && $debug) {
			$output['debug'] = $debug;
		}

		return $output;
	}

	/**
	 * Return API warning response array - Generic.
	 *
	 * @param string $msg Msg for the user.
	 * @param array<int|string, mixed> $additional Additonal data to attach to response.
	 * @param array<string, mixed> $debug Debug options.
	 *
	 * @return array<string, array<mixed>|int|string>
	 */
	public function getApiWarningOutput(string $msg, array $additional = [], array $debug = []): array
	{
		$output = [
			'status' => AbstractBaseRoute::STATUS_WARNING,
			'code' => 200,
			'message' => $msg,
		];

		if ($additional) {
			$output['data'] = $additional;
		}

		$isDeveloperMode = $this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_DEVELOPER_MODE_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY);

		if ($isDeveloperMode && $debug) {
			$output['debug'] = $debug;
		}

		return $output;
	}

	/**
	 * Return API error response array for missing permissions.
	 *
	 * @return array<string, mixed>
	 */
	public function getApiPermissionsErrorOutput(): array
	{
		return $this->getApiErrorOutput(
			\esc_html__('You don\'t have enough permissions to perform this action!', 'eightshift-forms'),
		);
	}
}
