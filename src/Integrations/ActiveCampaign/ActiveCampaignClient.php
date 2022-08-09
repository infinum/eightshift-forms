<?php

/**
 * ActiveCampaign Client integration class.
 *
 * @package EightshiftForms\Integrations\ActiveCampaign
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\ActiveCampaign;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Settings\SettingsHelper;

/**
 * ActiveCampaignClient integration class.
 */
class ActiveCampaignClient implements ClientInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Transient cache name for items.
	 */
	public const CACHE_ACTIVE_CAMPAIGN_ITEMS_TRANSIENT_NAME = 'es_active_campaign_items_cache';

	/**
	 * Transient cache name for item.
	 */
	public const CACHE_ACTIVE_CAMPAIGN_ITEM_TRANSIENT_NAME = 'es_active_campaign_item_cache';

	/**
	 * Return items.
	 *
	 * @param bool $hideUpdateTime Determin if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getItems(bool $hideUpdateTime = true): array
	{
		$output = \get_transient(self::CACHE_ACTIVE_CAMPAIGN_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Check if form exists in cache.
		if (empty($output)) {
			$items = $this->getActiveCampaignLists();

			if ($items) {
				foreach ($items as $item) {
					$id = $item['id'] ?? '';

					$output[$id] = [
						'id' => $id,
						'title' => $item['name'] ?? '',
					];
				}

				$output[ClientInterface::TRANSIENT_STORED_TIME] = [
					'id' => ClientInterface::TRANSIENT_STORED_TIME,
					'title' => \current_time('mysql'),
				];

				\set_transient(self::CACHE_ACTIVE_CAMPAIGN_ITEMS_TRANSIENT_NAME, $output, 3600);
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
		$output = \get_transient(self::CACHE_ACTIVE_CAMPAIGN_ITEM_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Check if form exists in cache.
		if (empty($output) || !isset($output[$itemId]) || empty($output[$itemId])) {
			$fields = $this->getActiveCampaignListFields($itemId);

			if ($itemId && $fields) {
				$output[$itemId] = $fields;

				\set_transient(self::CACHE_ACTIVE_CAMPAIGN_ITEM_TRANSIENT_NAME, $output, 3600);
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
		$body = [
			'contact' => $this->prepareParams($params),
		];

		$response = \wp_remote_request(
			"{$this->getBaseUrl()}contacts",
			[
				'headers' => $this->getHeaders(),
				'method' => 'POST',
				'body' => \wp_json_encode($body),
			]
		);

		error_log( print_r( ( $body ), true ) );
		

		if (\is_wp_error($response)) {
			Helper::logger([
				'integration' => 'activeCampaign',
				'type' => 'wp',
				'body' => $body,
				'response' => $response,
			]);

			return [
				'status' => 'error',
				'code' => 400,
				'message' => $this->getErrorMsg('submitWpError'),
			];
		}

		$code = $response['response']['code'] ? $response['response']['code'] : 200;

		if ($code === 200 || $code === 201) {
			return [
				'status' => 'success',
				'code' => $code,
				'message' => 'activeCampaignSuccess',
			];
		}

		$responseBody = \json_decode(\wp_remote_retrieve_body($response), true);
		$responseMessage = $responseBody['detail'] ?? '';
		$responseErrors = $responseBody['errors'] ?? [];

		error_log( print_r( ( $responseBody ), true ) );
		

		$output = [
			'status' => 'error',
			'code' => $code,
			'message' => $this->getErrorMsg($responseMessage, $responseErrors),
		];

		Helper::logger([
			'integration' => 'activeCampaign',
			'type' => 'service',
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
	 * @param array<string, mixed> $errors Additional errors got from the API.
	 *
	 * @return string
	 */
	private function getErrorMsg(string $msg, array $errors = []): string
	{
		if ($errors && isset($errors[0])) {
			$msg = $errors[0]['code'];
		}

		switch ($msg) {
			// case 'Bad Request':
			// 	return 'activeCampaignBadRequestError';
			// case "The resource submitted could not be validated. For field-specific details, see the 'errors' array.":
			// 	return 'activeCampaignInvalidResourceError';
			case 'contact_email_was_not_provided':
				return 'activeCampaignInvalidEmailError';
			case 'duplicate':
				return 'activeCampaignDuplicateError';
			// case 'Your merge fields were invalid.':
			// 	return 'activeCampaignMissingFieldsError';
			default:
				return 'submitWpError';
		}
	}

	/**
	 * Return ActiveCampaign tags for a list.
	 *
	 * @return array<int, mixed>
	 */
	private function getActiveCampaignTags(): array
	{
		$response = \wp_remote_get(
			"{$this->getBaseUrl()}tags",
			[
				'headers' => $this->getHeaders(),
				'timeout' => 60,
			]
		);

		$body = \json_decode(\wp_remote_retrieve_body($response), true);

		if (!isset($body['tags'])) {
			return [];
		}

		return $body['tags'];
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
			'Api-Token' => $this->getApiKey(),
		];

		return $headers;
	}

	/**
	 * API request to get one job by ID from ActiveCampaign.
	 *
	 * @param string $listId List id to search.
	 *
	 * @return array<string, mixed>
	 */
	private function getActiveCampaignListFields(string $listId)
	{
		$response = \wp_remote_get(
			"{$this->getBaseUrl()}forms/{$listId}",
			[
				'headers' => $this->getHeaders(),
				'timeout' => 60,
			]
		);

		$body = \json_decode(\wp_remote_retrieve_body($response), true);

		if (!isset($body['form']['cfields'])) {
			return [];
		}

		return $body['form']['cfields'];
	}

	/**
	 * API request to get all lists from ActiveCampaign.
	 *
	 * @return array<string, mixed>
	 */
	private function getActiveCampaignLists()
	{
		$response = \wp_remote_get(
			"{$this->getBaseUrl()}forms",
			[
				'headers' => $this->getHeaders(),
				'timeout' => 60,
			]
		);

		$body = \json_decode(\wp_remote_retrieve_body($response), true);

		if (!isset($body['forms'])) {
			return [];
		}

		return $body['forms'];
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

		$standardFields = array_flip(ActiveCampaign::STANDARD_FIELDS);

		if (isset($params['es-form-storage'])) {
			unset($params['es-form-storage']);
		}

		foreach ($params as $key => $param) {
			$value = $param['value'] ?? '';

			if (!$value) {
				continue;
			}

			if (isset($standardFields[$key])) {
				$output[$key] = $value; 
			} else {
				$output['fieldValues'][] = [
					'field' => $key,
					'value' => $value,
				];
			}
		}

		return $output;
	}

	/**
	 * Return ActiveCampaign base url.
	 *
	 * @return string
	 */
	private function getBaseUrl(): string
	{
		$url = rtrim($this->getApiUrl(), '/');

		return "{$url}/api/3/";
	}

	/**
	 * Return Api Key from settings or global vairaible.
	 *
	 * @return string
	 */
	private function getApiKey(): string
	{
		$apiKey = Variables::getApiKeyActiveCampaign();

		return !empty($apiKey) ? $apiKey : $this->getOptionValue(SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_API_KEY_KEY);
	}

	/**
	 * Return Api Url from settings or global vairaible.
	 *
	 * @return string
	 */
	private function getApiUrl(): string
	{
		$apiUrl = Variables::getApiUrlActiveCampaign();

		return !empty($apiUrl) ? $apiUrl : $this->getOptionValue(SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_API_URL_KEY);
	}
}
