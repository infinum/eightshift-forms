<?php

/**
 * Greenhouse Client integration class.
 *
 * @package EightshiftForms\Integrations\Greenhouse
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Greenhouse;

use CURLFile;
use EightshiftForms\General\General;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Rest\ApiHelper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

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
	 * Use API helper trait.
	 */
	use ApiHelper;

	/**
	 * Return Greenhouse base url.
	 *
	 * @var string
	 */
	private const BASE_URL = 'https://boards-api.greenhouse.io/v1/';

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
	 * @param bool $hideUpdateTime Determin if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getItems(bool $hideUpdateTime = true): array
	{
		$output = \get_transient(self::CACHE_GREENHOUSE_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

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
						'locations' => \explode(', ', $job['location']['name']),
						'updatedAt' => $job['updated_at'],
					];
				}

				$output[ClientInterface::TRANSIENT_STORED_TIME] = [
					'id' => ClientInterface::TRANSIENT_STORED_TIME,
					'title' => \current_datetime()->format('Y-m-d H:i:s'),
				];

				\set_transient(self::CACHE_GREENHOUSE_ITEMS_TRANSIENT_NAME, $output, 3600);
			}
		}

		if ($hideUpdateTime) {
			unset($output[ClientInterface::TRANSIENT_STORED_TIME]);
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
		$output = \get_transient(self::CACHE_GREENHOUSE_ITEM_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Check if form exists in cache.
		if (empty($output) || !isset($output[$itemId]) || empty($output[$itemId])) {
			$job = $this->getGreenhouseJob($itemId);

			$questions = $job['questions'] ?? [];

			if ($itemId && $questions) {
				$output[$itemId] = $job['questions'] ?? [];

				\set_transient(self::CACHE_GREENHOUSE_ITEM_TRANSIENT_NAME, $output, 3600);
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
		$paramsPrepared = $this->prepareParams($params);
		$paramsFiles = $this->prepareFiles($files);

		$body = \array_merge(
			$paramsPrepared,
			$paramsFiles
		);

		$filterName = Filters::getGeneralFilterName('httpRequestTimeout');

		$url = self::BASE_URL . "boards/{$this->getBoardToken()}/jobs/{$itemId}";

		// Curl used because files are not sent via wp request.
		$curl = \curl_init(); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_init
		\curl_setopt_array( // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt_array
			$curl,
			[
				\CURLOPT_URL => $url,
				\CURLOPT_HTTPAUTH => \CURLAUTH_BASIC,
				\CURLOPT_RETURNTRANSFER => true,
				\CURLOPT_TIMEOUT => \apply_filters($filterName, General::HTTP_REQUEST_TIMEOUT_DEFAULT),
				\CURLOPT_POST => true,
				\CURLOPT_POSTFIELDS => $body,
				\CURLOPT_HTTPHEADER => $this->getHeaders(true),
			]
		);
		$response = \curl_exec($curl); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_exec
		$code = \curl_getinfo($curl, \CURLINFO_RESPONSE_CODE); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_getinfo

		\curl_close($curl); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_close

		if (!$response) {
			$response = [
				'status' => 408,
				'error' => 'timeout',
			];
		} else {
			$response = \json_decode($response, true);
		}

		// Structure response details.
		$details = $this->getApiReponseDetails(
			SettingsGreenhouse::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$paramsPrepared,
			$paramsFiles,
			$itemId,
			$formId,
			true
		);

		$code = $details['code'];
		$body = $details['body'];

		// On success return output.
		if ($code >= 200 && $code <= 299) {
			return $this->getApiSuccessOutput($details);
		}

		// Output error.
		return $this->getApiErrorOutput(
			$details,
			$this->getErrorMsg($body)
		);
	}

	/**
	 * Map service messages with our own.
	 *
	 * @param array<mixed> $body API response body.
	 *
	 * @return string
	 */
	private function getErrorMsg(array $body): string
	{
		$msg = $body['error'] ?? '';

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
		$url = self::BASE_URL . "boards/{$this->getBoardToken()}/jobs";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		$details = $this->getApiReponseDetails(
			SettingsGreenhouse::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details['code'];
		$body = $details['body'];

		// On success return output.
		if ($code >= 200 && $code <= 299) {
			return $body['jobs'] ?? [];
		}

		return [];
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
		$url = self::BASE_URL . "boards/{$this->getBoardToken()}/jobs/{$jobId}?questions=true";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		$details = $this->getApiReponseDetails(
			SettingsGreenhouse::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details['code'];
		$body = $details['body'];

		// On success return output.
		if ($code >= 200 && $code <= 299) {
			return $body ?? [];
		}

		return [];
	}

	/**
	 * Set headers used for fetching data.
	 *
	 * @param boolean $postHeaders If using post method we need to send Authorization header and type in the request.
	 *
	 * @return array<int|string, string>
	 */
	private function getHeaders(bool $postHeaders = false): array
	{
		if ($postHeaders) {
			return [
				'Authorization: Basic ' . $this->getApiKey(),
				'Content-Type: multipart/form-data',
			];
		}

		return [
			'Content-Type' => 'application/json',
		];
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

		$customFields = \array_flip(Components::flattenArray(AbstractBaseRoute::CUSTOM_FORM_PARAMS));

		foreach ($params as $key => $param) {
			if (!isset($param['value'])) {
				continue;
			}

			// Get gh_src from url and map it.
			if ($key === AbstractBaseRoute::CUSTOM_FORM_PARAM_STORAGE && isset($param['value']['gh_src'])) {
				$output['mapped_url_token'] = $param['value']['gh_src'];
				continue;
			}

			// Remove unecesery fields.
			if (isset($customFields[$key])) {
				continue;
			}

			if (empty($param['value'])) {
				continue;
			}

			$output[$key] = $param['value'] ?? '';
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
				$type = $file['type'] ?? '';

				if (!$path) {
					continue;
				}

				$output[$id] = new CURLFile(\realpath($path), $type, $fileName); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
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

		return \base64_encode($apiKey ? $apiKey : $this->getOptionValue(SettingsGreenhouse::SETTINGS_GREENHOUSE_API_KEY_KEY)); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}
}
