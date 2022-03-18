<?php

/**
 * Greenhouse Client integration class.
 *
 * @package EightshiftForms\Integrations\Greenhouse
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Greenhouse;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;

/**
 * GreenhouseClient integration class.
 */
class GreenhouseClient implements ClientInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Transient cache name for items.
	 */
	public const CACHE_GREENHOUSE_ITEMS_TRANSIENT_NAME = 'es_greenhouse_items_cache';

	/**
	 * Transient cache name for item.
	 */
	public const CACHE_GREENHOUSE_ITEM_TRANSIENT_NAME = 'es_greenhouse_item_cache';

	/**
	 * Return items.
	 *
	 * @return array<string, mixed>
	 */
	public function getItems(): array
	{
		$output = get_transient(self::CACHE_GREENHOUSE_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Check if form exists in cache.
		if (empty($output)) {
			$jobs = $this->getGreenhouseJobs();

			if ($jobs) {
				foreach ($jobs as $job) {
					$jobId = $job['id'] ?? '';

					if (!$jobId) {
						continue;
					}

					$output[$jobId] = [
						'id' => (string) $jobId,
						'title' => $job['title'] ?? '',
						'locations' => explode(', ', $job['location']['name']),
						'updatedAt' => $job['updated_at'],
					];
				}

				set_transient(self::CACHE_GREENHOUSE_ITEMS_TRANSIENT_NAME, $output, 3600);
			}
		}

		return $output;
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
		$output = get_transient(self::CACHE_GREENHOUSE_ITEM_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Check if form exists in cache.
		if (empty($output) || !isset($output[$itemId]) || empty($output[$itemId])) {
			$job = $this->getGreenhouseJob($itemId);

			$questions = $job['questions'] ?? [];

			if ($itemId && $questions) {
				$output[$itemId] = $job['questions'] ?? [];

				set_transient(self::CACHE_GREENHOUSE_ITEM_TRANSIENT_NAME, $output, 3600);
			}
		}

		return $output[$itemId] ?? [];
	}

	/**
	 * API request to post application.
	 *
	 * @param string $itemId Item id to search.
	 * @param array<string, mixed> $params Params array.
	 * @param array<string, array<int, array<string, mixed>>> $files Files array.
	 * @param string $formId FormId value.
	 *
	 * @return array<string, mixed>
	 */
	public function postApplication(string $itemId, array $params, array $files, string $formId): array
	{
		$body = array_merge(
			$this->prepareParams($params),
			$this->prepareFiles($files)
		);

		$response = \wp_remote_post(
			"{$this->getBaseUrl()}boards/{$this->getBoardToken()}/jobs/{$itemId}",
			[
				'headers' => $this->getHeaders(true),
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

		if ($code === 200) {
			return [
				'status' => 'success',
				'code' => $code,
				'message' => 'greenhouseSuccess',
			];
		}

		$responseBody = json_decode(\wp_remote_retrieve_body($response), true);
		$responseMessage = $responseBody['error'] ?? '';

		$output = [
			'status' => 'error',
			'code' => $code,
			'message' => $this->getErrorMsg($responseMessage),
		];

		Helper::logger([
			'integration' => 'greenhouse',
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
				return 'greenhouseBadRequestError';
			case 'Uploaded resume has an unsupported file type.':
				return 'greenhouseUnsupportedFileTypeError';
			case 'Invalid attributes: first_name':
				return 'greenhouseInvalidFirstNameError';
			case 'Invalid attributes: last_name':
				return 'greenhouseInvalidLastNameError';
			case 'Invalid attributes: email':
				return 'greenhouseInvalidEmailError';
			case 'Invalid attributes: first_name,last_name,email':
				return 'greenhouseInvalidFirstNameLastNameEmailError';
			case 'Invalid attributes: first_name,last_name':
				return 'greenhouseInvalidFirstNameLastNameError';
			case 'Invalid attributes: first_name,email':
				return 'greenhouseInvalidFirstNameEmailError';
			case 'Invalid attributes: last_name,email':
				return 'greenhouseInvalidLastNameEmailError';
			case 'Invalid attributes: first_name,phone':
				return 'greenhouseInvalidFirstNamePhoneError';
			case 'Invalid attributes: last_name,phone':
				return 'greenhouseInvalidLastNamePhoneError';
			case 'Invalid attributes: email,phone':
				return 'greenhouseInvalidEmailPhoneError';
			case 'Invalid attributes: first_name,last_name,email,phone':
				return 'greenhouseInvalidFirstNameLastNameEmailPhoneError';
			case 'Invalid attributes: first_name,last_name,phone':
				return 'greenhouseInvalidFirstNameLastNamePhoneError';
			case 'Invalid attributes: first_name,email,phone':
				return 'greenhouseInvalidFirstNameEmailPhoneError';
			case 'Invalid attributes: last_name,email,phone':
				return 'greenhouseInvalidLastNameEmailPhoneError';
			default:
				return 'submitWpError';
		}
	}

	/**
	 * API request to get all jobs from Greenhouse.
	 *
	 * @return array<string, mixed>
	 */
	private function getGreenhouseJobs()
	{
		$response = \wp_remote_get(
			"{$this->getBaseUrl()}boards/{$this->getBoardToken()}/jobs",
			[
				'headers' => $this->getHeaders(),
				'timeout' => 60,
			]
		);

		$body = json_decode(\wp_remote_retrieve_body($response), true);

		if (!isset($body['jobs'])) {
			return [];
		}

		return $body['jobs'];
	}

	/**
	 * API request to get one job by ID from Greenhouse.
	 *
	 * @param string $jobId Job id to search.
	 *
	 * @return array<string, mixed>
	 */
	private function getGreenhouseJob(string $jobId)
	{
		$response = \wp_remote_get(
			"{$this->getBaseUrl()}boards/{$this->getBoardToken()}/jobs/{$jobId}?questions=true",
			[
				'headers' => $this->getHeaders(),
				'timeout' => 60,
			]
		);

		$body = json_decode(\wp_remote_retrieve_body($response), true);

		if (isset($body['error'])) {
			return [];
		}

		return $body;
	}

	/**
	 * Set headers used for fetching data.
	 *
	 * @param boolean $useAuth If using post method we need to send Authorization header in the request.
	 *
	 * @return array<string, mixed>
	 */
	private function getHeaders(bool $useAuth = false): array
	{
		$headers = [
			'Content-Type' => 'application/json; charset=utf-8',
		];

		if ($useAuth) {
			$headers['Authorization'] = "Basic {$this->getApiKey()}";
		}

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

		foreach ($params as $key => $value) {
			$output[$key] = $value['value'] ?? '';
		}

		return $output;
	}

	/**
	 * Prepare files.
	 *
	 * @param array<string, mixed> $files Files.
	 *
	 * @return array<string, mixed>
	 */
	private function prepareFiles(array $files): array
	{
		$output = [];

		foreach ($files as $items) {
			if (!$items) {
				continue;
			}

			foreach ($items as $file) {
				$fileName = $file['fileName'] ?? '';
				$path = $file['path'] ?? '';
				$id = $file['id'] ?? '';

				if (!$path || !$fileName || !$id) {
					continue;
				}

				$output["{$id}_content"] = base64_encode((string) file_get_contents($path)); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$output["{$id}_content_filename"] = $fileName;
			}
		}

		return $output;
	}

	/**
	 * Return Board Token from settings or global vairaible.
	 *
	 * @return string
	 */
	private function getBoardToken(): string
	{
		$boardToken = Variables::getBoardTokenGreenhouse();

		return $boardToken ? $boardToken : $this->getOptionValue(SettingsGreenhouse::SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY);
	}

	/**
	 * Return Api Key from settings or global vairaible.
	 *
	 * @return string
	 */
	private function getApiKey(): string
	{
		$apiKey = Variables::getApiKeyGreenhouse();

		return base64_encode($apiKey ? $apiKey : $this->getOptionValue(SettingsGreenhouse::SETTINGS_GREENHOUSE_API_KEY_KEY)); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Return Greenhouse base url.
	 *
	 * @return string
	 */
	private function getBaseUrl(): string
	{
		return 'https://boards-api.greenhouse.io/v1/';
	}
}
