<?php

/**
 * Airtable Client integration class.
 *
 * @package EightshiftForms\Integrations\Airtable
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Airtable;

use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\DeveloperHelpers;
use EightshiftForms\Helpers\HooksHelpers;

/**
 * AirtableClient integration class.
 */
class AirtableClient implements AirtableClientInterface
{
	/**
	 * Return Airtable base url.
	 *
	 * @var string
	 */
	private const BASE_URL = 'https://api.airtable.com/v0/';

	/**
	 * Transient cache name for items.
	 */
	public const CACHE_AIRTABLE_ITEMS_TRANSIENT_NAME = 'es_airtable_items_cache';

	/**
	 * Return items.
	 *
	 * @param bool $hideUpdateTime Determine if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getItems(bool $hideUpdateTime = true): array
	{
		$output = \get_transient(self::CACHE_AIRTABLE_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Prevent cache.
		if (DeveloperHelpers::isDeveloperSkipCacheActive()) {
			$output = [];
		}

		// Check if form exists in cache.
		if (!$output) {
			$items = $this->getAirtableLists();

			if ($items) {
				foreach ($items as $item) {
					$id = $item['id'] ?? '';

					$output[$id] = [
						'id' => (string) $id,
						'title' => $item['name'] ?? '',
						'items' => [],
						'records' => [],
					];
				}

				$output[ClientInterface::TRANSIENT_STORED_TIME] = [
					'id' => ClientInterface::TRANSIENT_STORED_TIME,
					'title' => \current_datetime()->format('Y-m-d H:i:s'),
				];

				\set_transient(self::CACHE_AIRTABLE_ITEMS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
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

		// Check if form exists in cache.
		if (empty($output) || !isset($output[$itemId]) || empty($output[$itemId]) || empty($output[$itemId]['items'])) {
			$fields = $this->getAirtableListFields($itemId);

			$tables = $fields['tables'] ?? [];

			if ($tables) {
				foreach ($tables as $item) {
					$id = $item['id'] ? (string) $item['id'] : '';
					$fields = $item['fields'] ?? [];

					$output[$itemId]['items'][$id] = [
						'id' => $id,
						'title' => $item['name'] ?? '',
						'primaryFieldId' => $item['primaryFieldId'] ?? '',
						'fields' => $fields,
					];
				}

				\set_transient(self::CACHE_AIRTABLE_ITEMS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
			}
		}

		return $output[$itemId]['items'] ?? [];
	}

	/**
	 * Return item details with cache option for faster loading.
	 *
	 * @param string $itemId Base ID to search by.
	 * @param string $listId List ID to search by.
	 *
	 * @return array<string, mixed>
	 */
	public function getItemDetails(string $itemId, string $listId): array
	{
		$output = $this->getItems();

		if (empty($output) || !isset($output[$itemId]) || empty($output[$itemId]) || empty($output[$itemId]['records']) || empty($output[$itemId]['records'][$listId])) {
			$fields = $this->getAirtableListRecords($itemId, $listId);

			$output[$itemId]['records'][$listId] = \array_map(
				static function ($item) {
					$fields = $item['fields'] ?? [];

					return [
						'id' => $item['id'] ?? '',
						'title' => \array_values($fields)[0] ?? '',
					];
				},
				$fields
			);

			\set_transient(self::CACHE_AIRTABLE_ITEMS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
		}

		return $output[$itemId]['records'][$listId] ?? [];
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
		$itemId = $formDetails[Config::FD_ITEM_ID] . Config::DELIMITER . $formDetails[Config::FD_INNER_ID];
		$params = $formDetails[Config::FD_PARAMS];
		$files = $formDetails[Config::FD_FILES];
		$formId = $formDetails[Config::FD_FORM_ID];

		// Filter override post request.
		$filterName = HooksHelpers::getFilterName(['integrations', SettingsAirtable::SETTINGS_TYPE_KEY, 'overridePostRequest']);
		if (\has_filter($filterName)) {
			$filterValue = \apply_filters($filterName, [], $itemId, $params, $files, $formId) ?? [];

			if ($filterValue) {
				return $filterValue;
			}
		}

		$body = [
			'fields' => $this->prepareParams($params),
		];

		$filterName = HooksHelpers::getFilterName(['integrations', SettingsAirtable::SETTINGS_TYPE_KEY, 'prePostId']);
		if (\has_filter($filterName)) {
			$itemId = \apply_filters($filterName, $itemId, $body, $formId) ?? $itemId;
		}

		$itemIdExploded = \explode(Config::DELIMITER, $itemId);

		$itemIdReal = $itemIdExploded[0] ?? '';
		$itemInnerIdReal = $itemIdExploded[1] ?? '';

		$url = self::BASE_URL . "{$itemIdReal}/{$itemInnerIdReal}";

		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode($body),
			]
		);

		// Structure response details.
		$details = ApiHelpers::getIntegrationApiResponseDetails(
			SettingsAirtable::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$body,
			$files,
			$itemId,
			$formId
		);

		$code = $details[Config::IARD_CODE];
		$body = $details[Config::IARD_BODY];

		// On success return output.
		if (ApiHelpers::isSuccessResponse($code)) {
			return ApiHelpers::getIntegrationSuccessInternalOutput($details);
		}

		$details[Config::IARD_MSG] = $this->getErrorMsg($body);

		// Output error.
		return ApiHelpers::getIntegrationErrorInternalOutput($details);
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
		$msg = $body['error']['type'] ?? '';

		switch ($msg) {
			case 'NOT_FOUND':
				return 'airtableNotFoundError';
			case 'INVALID_PERMISSIONS_OR_MODEL_NOT_FOUND':
				return 'airtableInvalidPermissionsOrModelNotFoundError';
			case 'INVALID_PERMISSIONS':
				return 'airtableInvalidPermissionsError';
			case 'INVALID_REQUEST_UNKNOWN':
				return 'airtableInvalidRequestUnknownError';
			case 'INVALID_VALUE_FOR_COLUMN':
				return 'airtableInvalidValueForColumnError';
			default:
				return 'submitWpError';
		}
	}

	/**
	 * Set headers used for fetching data.
	 *
	 * @return array<string, mixed>
	 */
	private function getHeaders(): array
	{
		$headers = [
			'Content-Type' => 'application/json; charset=utf-8',
			'Authorization' => "Bearer {$this->getApiKey()}",
		];

		return $headers;
	}

	/**
	 * API request to get one job by ID from Airtable.
	 *
	 * @param string $baseId Base id to search.
	 *
	 * @return array<string, mixed>
	 */
	private function getAirtableListFields(string $baseId)
	{
		$url = self::BASE_URL . "meta/bases/{$baseId}/tables";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		$details = ApiHelpers::getIntegrationApiResponseDetails(
			SettingsAirtable::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details[Config::IARD_CODE];
		$body = $details[Config::IARD_BODY];

		// On success return output.
		if (ApiHelpers::isSuccessResponse($code)) {
			return $body ?? [];
		}

		return [];
	}

	/**
	 * API request to get one job by ID from Airtable.
	 *
	 * @param string $baseId Base id to search.
	 * @param string $listId List id to search.
	 * @param string $offset Offset value.
	 *
	 * @return array<string, mixed>
	 */
	private function getAirtableListRecords(string $baseId, string $listId, string $offset = ''): array
	{
		$url = self::BASE_URL . "{$baseId}/{$listId}";

		if ($offset) {
			$url .= "?offset={$offset}";
		}

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		$details = ApiHelpers::getIntegrationApiResponseDetails(
			SettingsAirtable::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details[Config::IARD_CODE];
		$body = $details[Config::IARD_BODY];

		// On success return output.
		if (ApiHelpers::isSuccessResponse($code)) {
			$data = $body['records'] ?? [];
			$offset = $body['offset'] ?? '';

			// If we have more that 100 records, we need to fetch them all.
			if ($offset) {
				$data = \array_merge($data, $this->getAirtableListRecords($baseId, $listId, $offset));
			}

			return $data;
		}


		return [];
	}

	/**
	 * Get test api.
	 *
	 * @return array<mixed>
	 */
	public function getTestApi(): array
	{
		$url = self::BASE_URL . "meta/bases";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		return ApiHelpers::getIntegrationApiResponseDetails(
			SettingsAirtable::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);
	}

	/**
	 * API request to get all lists from Airtable.
	 *
	 * @return array<string, mixed>
	 */
	private function getAirtableLists()
	{
		$details = $this->getTestApi();

		$code = $details[Config::IARD_CODE];
		$body = $details[Config::IARD_BODY];

		// On success return output.
		if (ApiHelpers::isSuccessResponse($code)) {
			return $body['bases'] ?? [];
		}

		return [];
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

		// Remove unnecessary params.
		$params = GeneralHelpers::removeUnnecessaryParamFields($params);

		foreach ($params as $param) {
			$value = $param['value'] ?? '';
			$name = $param['name'] ?? '';

			if (!$value || !$name) {
				continue;
			}

			switch ($param['typeCustom'] ?? '') {
				case 'singleCheckbox':
					$value = \filter_var(($value[0] ?? ''), \FILTER_VALIDATE_BOOLEAN);
					break;
				case 'number':
					$value = \filter_var($value, \FILTER_VALIDATE_FLOAT);
					break;
				default:
					$value = $value;
					break;
			}

			$output[$name] = $value;
		}

		return $output;
	}

	/**
	 * Return Api Key from settings or global variable.
	 *
	 * @return string
	 */
	private function getApiKey(): string
	{
		return SettingsHelpers::getOptionWithConstant(Variables::getApiKeyAirtable(), SettingsAirtable::SETTINGS_AIRTABLE_API_KEY_KEY);
	}
}
