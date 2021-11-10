<?php

/**
 * Goodbits Client integration class.
 *
 * @package EightshiftForms\Integrations\Goodbits
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Goodbits;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\ObjectHelperTrait;

/**
 * GoodbitsClient integration class.
 */
class GoodbitsClient implements ClientInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Helper trait.
	 */
	use ObjectHelperTrait;

	/**
	 * Return items.
	 *
	 * @return array<string, mixed>
	 */
	public function getItems(): array
	{
		$apiKey = Variables::getApiKeyGoodbits();

		$key = !empty($apiKey) ? $apiKey : $this->getOptionValue(SettingsGoodbits::SETTINGS_GOODBITS_API_KEY_KEY);

		if (is_array($key)) {
			return array_map(
				function($title, $id) {
					return [
						'title' => $title,
						'id' => $id,
					];
				},
				array_keys($key),
				$key
			);
		}

		return [
			[
				'title' => __('Goodbits', 'eightshift-forms'),
				'id' => $key,
			],
		];
	}

	/**
	 * Return item with cache option for faster loading.
	 *
	 * @param string $itemId Item ID to search by.
	 *
	 * @return array<string, mixed>
	 */
	public function getItem(string $itemId): array
	{
		return [];
	}

	/**
	 * API request to post application.
	 *
	 * @param string $itemId Item id to search.
	 * @param array<string, mixed> $params Params array.
	 * @param array<string, mixed> $files Files array.
	 *
	 * @return array<string, mixed>
	 */
	public function postApplication(string $itemId, array $params, array $files): array
	{
		$email = $params['email']['value'];

		$body = [
			'subscriber' => $this->prepareParams($params),
		];

		$response = \wp_remote_request(
			"{$this->getBaseUrl()}subscribers/{$email}",
			[
				'headers' => $this->getHeaders($itemId),
				'method' => 'PUT',
				'body' => wp_json_encode($body),
			]
		);

		if (is_wp_error($response)) {
			return [
				'status' => 'error',
				'code' => 400,
				'message' => $this->getErrorMsg('submitWpError'),
			];
		}

		
		$code = $response['response']['code'] ?? 200;
		
		error_log( print_r( ( $response ), true ) );
		if ($code === 200 || $code === 201) {
			return [
				'status' => 'success',
				'code' => 200,
				'message' => 'goodbitsSuccess',
			];
		}

		$responseBody = json_decode(\wp_remote_retrieve_body($response), true);
		$responseMessage = $responseBody['error']['message'] ?? '';

		$output = [
			'status' => 'error',
			'code' => $code,
			'message' => $this->getErrorMsg($responseMessage),
		];

		Helper::logger([
			'integration' => 'goodbits',
			'body' => $body,
			'response' => $response['response'],
			'responseBody' => $responseBody,
			'output' => $output,
		]);

		return $output;
	}

	/**
	 * Map service messages with our own.
	 *
	 * @param string $msg Message got from the API.
	 *
	 * @return string
	 */
	private function getErrorMsg(string $msg): string
	{
		switch ($msg) {
			case 'Bad Request':
				return 'goodbitsBadRequestError';
			case 'Invalid email address':
				return 'goodbitsInvalidEmailError';
			case 'Email temporarily blocked':
				return 'goodbitsEmailTemporarilyBlockedError';
			default:
				return 'submitWpError';
		}
	}

	/**
	 * Set headers used for fetching data.
	 *
	 * @param string $itemId Name of the api key.
	 *
	 * @return array<string, mixed>
	 */
	private function getHeaders(string $itemId): array
	{
		$headers = [
			'Content-Type' => 'application/json; charset=utf-8',
			'Authorization' => $itemId,
		];

		return $headers;
	}

	/**
	 * Prepare params
	 *
	 * @param array<string, mixed> $params Params.
	 *
	 * @return array<string, mixed>
	 */
	private function prepareParams(array $params): array
	{
		$output = [];

		unset($params['email']);

		foreach ($params as $key => $value) {
			$output[$key] = $value['value'] ?? '';
		}

		return $output;
	}

	/**
	 * Return Goodbits base url.
	 *
	 * @return string
	 */
	private function getBaseUrl(): string
	{
		return "https://app.goodbits.io/api/v1/";
	}

	/**
	 * Return Api Keys from settings or global vairaible.
	 *
	 * @return string
	 */
	private function getApiKeys(): array
	{
		$apiKey = Variables::getApiKeyGoodbits();

		$key = !empty($apiKey) ? $apiKey : $this->getOptionValue(SettingsGoodbits::SETTINGS_GOODBITS_API_KEY_KEY);

		if (is_array($key)) {
			return array_map(
				function($title, $id) {
					return [
						'title' => $title,
						'id' => $id,
					];
				},
				array_keys($key),
				$key
			);
		}

		return [
			[
				'title' => __('Goodbits', 'eightshift-forms'),
				'id' => $key,
			],
		];
	}
}
