<?php

/**
 * Greenhouse Client integration class.
 *
 * @package EightshiftForms\Integrations\Greenhouse
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Greenhouse;

use CURLFile;
use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\General\General;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;

/**
 * GreenhouseClient integration class.
 */
class GreenhouseClient implements ClientInterface
{
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
	 * Return items.
	 *
	 * @param bool $hideUpdateTime Determin if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getItems(bool $hideUpdateTime = true): array
	{
		$output = \get_transient(self::CACHE_GREENHOUSE_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Prevent cache.
		if (UtilsDeveloperHelper::isDeveloperSkipCacheActive()) {
			$output = [];
		}

		// Check if form exists in cache.
		if (!$output) {
			$items = $this->getGreenhouseItems();

			if ($items) {
				foreach ($items as $item) {
					$id = $item['id'] ?? '';

					if (!$id) {
						continue;
					}

					$output[$id] = [
						'id' => (string) $id,
						'title' => $item['title'] ?? '',
						'locations' => \explode(', ', $item['location']['name']),
						'fields' => [],
						'updatedAt' => $item['updated_at'],
					];
				}

				$output[ClientInterface::TRANSIENT_STORED_TIME] = [
					'id' => ClientInterface::TRANSIENT_STORED_TIME,
					'title' => \current_datetime()->format('Y-m-d H:i:s'),
				];

				\set_transient(self::CACHE_GREENHOUSE_ITEMS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
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
		$output = $this->getItems();

		$item = $output[$itemId]['fields'] ?? [];

		// Check if form exists in cache.
		if (!$output || !$item) {
			$items = $this->getGreenhouseItem($itemId)['questions'] ?? [];

			if ($items) {
				$output[$itemId]['fields'] = $items;

				\set_transient(self::CACHE_GREENHOUSE_ITEMS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
			}
		}

		return $output[$itemId] ?? [];
	}

	/**
	 * API request to post application.
	 *
	 * @param array<string, mixed> $formDetails Form details.
	 *
	 * @return array<string, mixed>
	 */
	public function postApplication(array $formDetails): array
	{
		$itemId = $formDetails[UtilsConfig::FD_ITEM_ID];
		$params = $formDetails[UtilsConfig::FD_PARAMS];
		$files = $formDetails[UtilsConfig::FD_FILES];
		$formId = $formDetails[UtilsConfig::FD_FORM_ID];

		// Filter override post request.
		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsGreenhouse::SETTINGS_TYPE_KEY, 'overridePostRequest']);
		if (\has_filter($filterName)) {
			$filterValue = \apply_filters($filterName, [], $itemId, $params, $files, $formId) ?? [];

			if ($filterValue) {
				return $filterValue;
			}
		}

		$paramsPrepared = $this->prepareParams($params);
		$paramsFiles = $this->prepareFiles($files);

		$body = \array_merge(
			$paramsPrepared,
			$paramsFiles
		);

		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsGreenhouse::SETTINGS_TYPE_KEY, 'prePostId']);
		if (\has_filter($filterName)) {
			$itemId = \apply_filters($filterName, $itemId, $paramsPrepared, $formId) ?? $itemId;
		}

		$filterName = UtilsHooksHelper::getFilterName(['general', 'httpRequestTimeout']);

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
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsGreenhouse::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$paramsPrepared,
			$paramsFiles,
			$itemId,
			$formId,
			UtilsSettingsHelper::isOptionCheckboxChecked(SettingsGreenhouse::SETTINGS_GREENHOUSE_SKIP_INTEGRATION_KEY, SettingsGreenhouse::SETTINGS_GREENHOUSE_SKIP_INTEGRATION_KEY),
			true
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return UtilsApiHelper::getIntegrationSuccessInternalOutput($details);
		}

		$details[UtilsConfig::IARD_VALIDATION] = $this->getFieldsErrors($body);
		$details[UtilsConfig::IARD_MSG] = $this->getErrorMsg($body);

		// Output error.
		return UtilsApiHelper::getIntegrationErrorInternalOutput($details);
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
			default:
				return 'submitWpError';
		}
	}


	/**
	 * Map service messages for fields with our own.
	 *
	 * @param array<mixed> $body API response body.
	 *
	 * @return array<string, string>
	 */
	private function getFieldsErrors(array $body): array
	{
		$msg = $body['error'] ?? '';
		$output = [];

		// Validate req fields.
		\preg_match_all("/(Invalid attributes: )([a-zA-Z0-9_,]*)/", $msg, $matchesReq, \PREG_SET_ORDER, 0);

		if ($matchesReq) {
			$key = $matchesReq[0][2] ?? '';
			if ($key) {
				$keys = \explode(',', $key);

				foreach ($keys as $inner) {
					$output[$inner] = 'validationRequired';
				}
			}
		}

		if (\strpos($msg, 'Uploaded resume has an unsupported file type.') !== false) {
			$output['resume'] = 'validationGreenhouseAcceptMime';
		}

		if (\strpos($msg, 'Uploaded cover letter has an unsupported file type') !== false) {
			$output['cover_letter'] = 'validationGreenhouseAcceptMime';
		}

		return $output;
	}

	/**
	 * Get test api.
	 *
	 * @return array<mixed>
	 */
	public function getTestApi(): array
	{
		$url = self::BASE_URL . "boards/{$this->getBoardToken()}/jobs";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		return UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsGreenhouse::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);
	}

	/**
	 * API request to get all jobs from Greenhouse.
	 *
	 * @return array<string, mixed>
	 */
	private function getGreenhouseItems()
	{
		$details = $this->getTestApi();

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
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
	private function getGreenhouseItem(string $jobId)
	{
		$url = self::BASE_URL . "boards/{$this->getBoardToken()}/jobs/{$jobId}?questions=true";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsGreenhouse::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return $body ?? [];
		}

		return [];
	}

	/**
	 * Set headers used for fetching data.
	 *
	 * @param boolean $isCurl If using post method we need to send Authorization header and type in the request.
	 *
	 * @return array<int|string, string>
	 */
	private function getHeaders(bool $isCurl = false): array
	{
		if ($isCurl) {
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
		// Remove unecesery params.
		$params = UtilsGeneralHelper::removeUneceseryParamFields($params);

		return UtilsGeneralHelper::prepareGenericParamsOutput($params);
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
			$name = $items['name'] ?? '';
			$value = $items['value'] ?? [];

			if (!$name || !$value) {
				continue;
			}

			foreach ($value as $file) {
				$output[$name] = new CURLFile($file); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
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
		return UtilsSettingsHelper::getOptionWithConstant(Variables::getBoardTokenGreenhouse(), SettingsGreenhouse::SETTINGS_GREENHOUSE_BOARD_TOKEN_KEY);
	}

	/**
	 * Return Api Key from settings or global vairaible.
	 *
	 * @return string
	 */
	private function getApiKey(): string
	{
		return \base64_encode(UtilsSettingsHelper::getOptionWithConstant(Variables::getApiKeyGreenhouse(), SettingsGreenhouse::SETTINGS_GREENHOUSE_API_KEY_KEY)); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}
}
