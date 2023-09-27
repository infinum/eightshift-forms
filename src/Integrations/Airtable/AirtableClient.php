<?php

/**
 * Airtable Client integration class.
 *
 * @package EightshiftForms\Integrations\Airtable
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Airtable;

use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Rest\ApiHelper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Troubleshooting\SettingsDebug;

/**
 * AirtableClient integration class.
 */
class AirtableClient implements ClientInterface
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
	 * Instance variable of enrichment data.
	 *
	 * @var EnrichmentInterface
	 */
	protected EnrichmentInterface $enrichment;

	/**
	 * Create a new admin instance.
	 *
	 * @param EnrichmentInterface $enrichment Inject enrichment which holds data about for storing to localStorage.
	 */
	public function __construct(EnrichmentInterface $enrichment)
	{
		$this->enrichment = $enrichment;
	}

	/**
	 * Return items.
	 *
	 * @param bool $hideUpdateTime Determin if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getItems(bool $hideUpdateTime = true): array
	{
		$output = \get_transient(self::CACHE_AIRTABLE_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Prevent cache.
		if ($this->isOptionCheckboxChecked(SettingsDebug::SETTINGS_DEBUG_SKIP_CACHE_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY)) {
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
		$output = \get_transient(self::CACHE_AIRTABLE_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Prevent cache.
		if ($this->isOptionCheckboxChecked(SettingsDebug::SETTINGS_DEBUG_SKIP_CACHE_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY)) {
			$output = [];
		}

		// Check if form exists in cache.
		if (empty($output) || !isset($output[$itemId]) || empty($output[$itemId]) || empty($output[$itemId]['items'])) {
			$fields = $this->getAirtableListFields($itemId);

			$tables = $fields['tables'] ?? [];

			if ($itemId && $tables) {
				foreach ($tables as $item) {
					$id = $item['id'] ? (string) $item['id'] : '';

					$output[$itemId]['items'][$id] = [
						'id' => $id,
						'title' => $item['name'] ?? '',
						'primaryFieldId' => $item['primaryFieldId'] ?? '',
						'fields' => $item['fields'] ?? [],
					];
				}

				\set_transient(self::CACHE_AIRTABLE_ITEMS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
			}
		}

		return $output[$itemId]['items'] ?? [];
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
		$itemIdExploded = \explode(AbstractBaseRoute::DELIMITER, $itemId);

		$body = [
			'fields' => $this->prepareParams($params),
		];

		$url = self::BASE_URL . "{$itemIdExploded[0]}/{$itemIdExploded[1]}";

		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode($body),
			]
		);

		// Structure response details.
		$details = $this->getIntegrationApiReponseDetails(
			SettingsAirtable::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$body,
			$files,
			$itemId,
			$formId,
			$this->isOptionCheckboxChecked(SettingsAirtable::SETTINGS_AIRTABLE_SKIP_INTEGRATION_KEY, SettingsAirtable::SETTINGS_AIRTABLE_SKIP_INTEGRATION_KEY)
		);

		$code = $details['code'];
		$body = $details['body'];

		// On success return output.
		if ($code >= 200 && $code <= 299) {
			return $this->getIntegrationApiSuccessOutput($details);
		}

		// Output error.
		return $this->getIntegrationApiErrorOutput(
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
		$details = $this->getIntegrationApiReponseDetails(
			SettingsAirtable::SETTINGS_TYPE_KEY,
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
		return $this->getIntegrationApiReponseDetails(
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

		$code = $details['code'];
		$body = $details['body'];

		// On success return output.
		if ($code >= 200 && $code <= 299) {
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

		// Map enrichment data.
		$params = $this->enrichment->mapEnrichmentFields($params);

		// Remove unecesery params.
		$params = Helper::removeUneceseryParamFields($params);

		$filterName = Filters::getFilterName(['integrations', SettingsAirtable::SETTINGS_TYPE_KEY, 'prePostParams']);
		if (\has_filter($filterName)) {
			$params = \apply_filters($filterName, $params) ?? [];
		}

		foreach ($params as $param) {
			$value = $param['value'] ?? '';
			if (!$value) {
				continue;
			}

			$name = $param['name'] ?? '';
			if (!$name) {
				continue;
			}

			switch ($param['typeCustom'] ?? '') {
				case 'singleCheckbox':
					$value = \filter_var($value, \FILTER_VALIDATE_BOOLEAN);
					break;
				case 'number':
					$value = \filter_var($value, \FILTER_VALIDATE_FLOAT);
					break;
				case 'multiCheckbox':
					$value = \explode(AbstractBaseRoute::DELIMITER, $value);
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
	 * Return Api Key from settings or global vairaible.
	 *
	 * @return string
	 */
	private function getApiKey(): string
	{
		$apiKey = Variables::getApiKeyAirtable();

		return !empty($apiKey) ? $apiKey : $this->getOptionValue(SettingsAirtable::SETTINGS_AIRTABLE_API_KEY_KEY);
	}
}
