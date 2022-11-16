<?php

/**
 * Airtable Client integration class.
 *
 * @package EightshiftForms\Integrations\Airtable
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Airtable;

use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Rest\ApiHelper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

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
	 * Return items.
	 *
	 * @param bool $hideUpdateTime Determin if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getItems(bool $hideUpdateTime = true): array
	{
		$output = \get_transient(self::CACHE_AIRTABLE_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Check if form exists in cache.
		if (empty($output)) {
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

				\set_transient(self::CACHE_AIRTABLE_ITEMS_TRANSIENT_NAME, $output, 3600);
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

				\set_transient(self::CACHE_AIRTABLE_ITEMS_TRANSIENT_NAME, $output, 3600);
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
		$itemIdExploded = \explode('---', $itemId);

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
		$details = $this->getApiReponseDetails(
			SettingsAirtable::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$body,
			$files,
			$itemId,
			$formId
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
		$details = $this->getApiReponseDetails(
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
	 * API request to get all lists from Airtable.
	 *
	 * @return array<string, mixed>
	 */
	private function getAirtableLists()
	{
		$url = self::BASE_URL . "meta/bases";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		$details = $this->getApiReponseDetails(
			SettingsAirtable::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

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

		$customFields = \array_flip(Components::flattenArray(AbstractBaseRoute::CUSTOM_FORM_PARAMS));

		foreach ($params as $key => $param) {
			// Remove unnecessary fields.
			if (isset($customFields[$key])) {
				continue;
			}

			switch ($param['internalType']) {
				case 'singleCheckbox':
					$value = \filter_var($param['value'], \FILTER_VALIDATE_BOOLEAN);
					break;
				case 'number':
					if ($param['value']) {
						$value = \filter_var($param['value'], \FILTER_VALIDATE_FLOAT);
					} else {
						$value = 0;
					}
					break;
				case 'multiCheckbox':
					if ($param['value']) {
						$value = \explode(', ', $param['value']);
					} else {
						$value = [];
					}
					break;
				default:
					$value = $param['value'];
					break;
			}

			$output[$key] = $value;
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
