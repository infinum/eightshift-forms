<?php

/**
 * Trait that holds all api helpers used in classes.
 *
 * @package EightshiftLibs\Rest
 */

declare(strict_types=1);

namespace EightshiftForms\Rest;

use EightshiftForms\Helpers\Helper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

/**
 * ApiHelper trait.
 */
trait ApiHelper
{
	/**
	 * Return API response array with logger.
	 *
	 * @param string $integration Integration name.
	 * @param array<string, mixed> $details Details from response.
	 * @param array<string, mixed> $requestBody Request body sent to the API.
	 * @param string $msg Msg for the user.
	 *
	 * @return array<string, string|int>
	 */
	public function getApiWpErrorOutput(
		string $integration,
		array $details,
		array $requestBody,
		string $msg
	): array {
		Helper::logger([
			'integration' => Components::kebabToCamelCase($integration, '-'),
			'type' => 'wp',
			'response' => $details,
			'requestBody' => $requestBody,
			'msg' => $msg,
		]);

		return [
			'status' => 'error',
			'code' => 400,
			'message' => 'submitWpError',
		];
	}

	/**
	 * Return API response array details.
	 *
	 * @param array<mixed> $response Response got from the API.
	 *
	 * @return array<string, mixed>
	 */
	public function getApiReponseDetails($response): array
	{
		return [
			'code' => $response['response']['code'] ? $response['response']['code'] : 200,
			'body' => \json_decode(\wp_remote_retrieve_body($response), true) ?? [],
			'response' => $response['response'] ?? [],
			'url' => $response['url'] ?? '',
		];
	}

	/**
	 * Return API error response array with logger.
	 *
	 * @param string $integration Integration name.
	 * @param array<string, mixed> $details Details from response.
	 * @param array<string, mixed> $requestBody Request body sent to the API.
	 * @param string $msg Msg for the user.
	 *
	 * @return array<string, string|int>
	 */
	public function getApiErrorOutput(
		string $integration,
		array $details,
		array $requestBody,
		string $msg
	): array {
		// Log output.
		Helper::logger([
			'integration' => Components::kebabToCamelCase($integration, '-'),
			'type' => 'service',
			'response' => $details,
			'requestBody' => $requestBody,
			'msg' => $msg,
		]);

		return [
			'status' => 'error',
			'code' => $details['code'],
			'message' => $msg,
		];
	}

	/**
	 * Return API success response array.
	 *
	 * @param string $integration Integration name.
	 * @param array<string, mixed> $additional Additional array details to attach to the success output.
	 *
	 * @return array<string, string|int>
	 */
	public function getApiSuccessOutput(string $integration, array $additional = []): array
	{
		$integration = Components::kebabToCamelCase($integration, '-');

		return \array_merge(
			[
				'status' => 'success',
				'code' => 200,
				'message' => "{$integration}Success",
			],
			$additional
		);
	}
}
