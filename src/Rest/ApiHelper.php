<?php

/**
 * Trait that holds all api helpers used in classes.
 *
 * @package EightshiftLibs\Rest
 */

declare(strict_types=1);

namespace EightshiftForms\Rest;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Troubleshooting\SettingsTroubleshooting;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use WP_Error;

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
	 * @param array<mixed>|WP_Error $response API full reponse.
	 * @param string $url Url of the request.
	 * @param array<mixed> $params All params prepared for API.
	 * @param array<mixed> $files All files prepared for API.
	 * @param string $listId List Id used for API (questions, form id, list id, item id).
	 * @param string $formId Internal form ID.
	 * @param boolean $isCurl Used for some changed if native cURL is used.
	 *
	 * @return array<string, mixed>
	 */
	public function getApiReponseDetails(
		string $integration,
		$response,
		string $url,
		array $params = [],
		array $files = [],
		string $listId = '',
		string $formId = '',
		bool $isCurl = false
	): array {

		// Do regular stuff if this is not and WP_Error.
		if (!is_wp_error($response)) {
			if ($isCurl) {
				$code = $response['status'] ?? 200;
				$body = $response;
			} else {
				$code = $response['response']['code'] ?? 200;
				$body = \json_decode($response['body'] ?? '', true) ?? [];
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
			'body' => $body,
			'url' => $url,
			'listId' => $listId,
			'formId' => $formId,
		];
	}

	/**
	 * Return API error response array with logger.
	 *
	 * @param array<string, mixed> $details Details provided by getApiReponseDetails method.
	 * @param string $msg Msg for the user.
	 *
	 * @return array<string, mixed>
	 */
	public function getApiErrorOutput(array $details, string $msg): array
	{
		if ($this->isCheckboxOptionChecked(SettingsTroubleshooting::SETTINGS_TROUBLESHOOTING_LOG_MODE_KEY, SettingsTroubleshooting::SETTINGS_TROUBLESHOOTING_DEBUGGING_KEY)) {
			Helper::logger($details);
		}

		return [
			'status' => 'error',
			'code' => $details['code'] ?? 400,
			'message' => $msg,
			'data' => $details,
		];
	}

	/**
	 * Return API success response array.
	 *
	 * @param array<string, mixed> $details Details provided by getApiReponseDetails method.
	 * @param array<string, mixed> $additional Additional array details to attach to the success output.
	 *
	 * @return array<string, mixed>
	 */
	public function getApiSuccessOutput(array $details, array $additional = []): array
	{

		$integration = $details['integration'] ?? '';

		return \array_merge(
			[
				'status' => 'success',
				'code' => $details['code'] ?? 200,
				'message' => "{$integration}Success",
			],
			$additional
		);
	}
}
