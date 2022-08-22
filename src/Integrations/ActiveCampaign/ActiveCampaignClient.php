<?php

/**
 * ActiveCampaign Client integration class.
 *
 * @package EightshiftForms\Integrations\ActiveCampaign
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\ActiveCampaign;

use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ActiveCampaign\ActiveCampaignClientInterface;
use EightshiftForms\Rest\ApiHelper;
use EightshiftForms\Settings\SettingsHelper;

/**
 * ActiveCampaignClient integration class.
 */
class ActiveCampaignClient implements ActiveCampaignClientInterface
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

				$output[ActiveCampaignClientInterface::TRANSIENT_STORED_TIME] = [
					'id' => ActiveCampaignClientInterface::TRANSIENT_STORED_TIME,
					'title' => \current_datetime()->format('Y-m-d H:i:s'),
				];

				\set_transient(self::CACHE_ACTIVE_CAMPAIGN_ITEMS_TRANSIENT_NAME, $output, 3600);
			}
		}

		if ($hideUpdateTime) {
			unset($output[ActiveCampaignClientInterface::TRANSIENT_STORED_TIME]);
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
		// Map body.
		$requestBody = [
			'contact' => $this->prepareParams($params),
		];

		// Make an API request.
		$response = \wp_remote_request(
			"{$this->getBaseUrl()}contacts",
			[
				'headers' => $this->getHeaders(),
				'method' => 'POST',
				'body' => \wp_json_encode($requestBody),
			]
		);

		// Output response details.
		$details = $this->getApiReponseDetails($response);
		$code = $details['code'];
		$body = $details['body'];

		// Bailout if wp error.
		if (\is_wp_error($response)) {
			return $this->getApiWpErrorOutput(
				SettingsActiveCampaign::SETTINGS_TYPE_KEY,
				$details,
				$requestBody,
				$response->get_error_message()
			);
		}

		switch ($code) {
			case 200:
			case 201:
				return $this->getApiSuccessOutput(
					SettingsActiveCampaign::SETTINGS_TYPE_KEY,
					[
						'contactId' => $body['fieldValues'][0]['contact'],
					]
				);
			case 403:
				return $this->getApiErrorOutput(
					SettingsActiveCampaign::SETTINGS_TYPE_KEY,
					$details,
					$requestBody,
					$this->getErrorMsg([
						[
							'code' => 'activeCampaignForbidden',
						]
					]),
				);
			case 500:
				return $this->getApiErrorOutput(
					SettingsActiveCampaign::SETTINGS_TYPE_KEY,
					$details,
					$requestBody,
					$this->getErrorMsg([
						[
							'code' => 'activeCampaign500',
						]
					]),
				);
			default:
				return $this->getApiErrorOutput(
					SettingsActiveCampaign::SETTINGS_TYPE_KEY,
					$details,
					$requestBody,
					$this->getErrorMsg($body['errors'] ?? []),
				);
		}
	}

	/**
	 * API request to post tags.
	 *
	 * @param string $tag Tag to store.
	 * @param string $contactId Contact ID to store.
	 *
	 * @return array<string, mixed>
	 */
	public function postTag(string $tag, string $contactId): array
	{
		// Check if tag exist using api.
		$tagId = $this->getExistingTagId($tag);

		// If tag is missing create new using api.
		if (!$tagId) {
			$tagId = $this->createNewTag($tag);
		}

		// Prepare body.
		$requestBody = [
			'contactTag' => [
				'contact' => $contactId,
				'tag' => $tagId,
			],
		];

		// Make request to map contact with tags.
		$response = \wp_remote_request(
			"{$this->getBaseUrl()}contactTags",
			[
				'headers' => $this->getHeaders(),
				'method' => 'POST',
				'body' => \wp_json_encode($requestBody),
			]
		);

		// Output response details.
		$details = $this->getApiReponseDetails($response);
		$code = $details['code'];
		$body = $details['body'];

		// Bailout on wp error.
		if (\is_wp_error($response)) {
			return $this->getApiWpErrorOutput(
				SettingsActiveCampaign::SETTINGS_TYPE_KEY,
				$details,
				$requestBody,
				$response->get_error_message()
			);
		}

		switch ($code) {
			case 200:
			case 201:
				return $this->getApiSuccessOutput(
					SettingsActiveCampaign::SETTINGS_TYPE_KEY,
				);
			default:
				return $this->getApiErrorOutput(
					SettingsActiveCampaign::SETTINGS_TYPE_KEY,
					$details,
					$requestBody,
					$this->getErrorMsg($body['errors'] ?? []),
				);
		}
	}

	/**
	 * API request to post list.
	 *
	 * @param string $list List to store.
	 * @param string $contactId Contact ID to store.
	 *
	 * @return array<string, mixed>
	 */
	public function postList(string $list, string $contactId): array
	{
		// Prepare body.
		$requestBody = [
			'contactList' => [
				'contact' => $contactId,
				'list' => $list,
				'status' => '1',
			],
		];

		// Make request to map contact with lists.
		$response = \wp_remote_request(
			"{$this->getBaseUrl()}contactLists",
			[
				'headers' => $this->getHeaders(),
				'method' => 'POST',
				'body' => \wp_json_encode($requestBody),
			]
		);

		// Output response details.
		$details = $this->getApiReponseDetails($response);
		$code = $details['code'];
		$body = $details['body'];

		// Bailout on wp error.
		if (\is_wp_error($response)) {
			return $this->getApiWpErrorOutput(
				SettingsActiveCampaign::SETTINGS_TYPE_KEY,
				$details,
				$requestBody,
				$response->get_error_message()
			);
		}

		switch ($code) {
			case 200:
			case 201:
				return $this->getApiSuccessOutput(
					SettingsActiveCampaign::SETTINGS_TYPE_KEY,
				);
			default:
				return $this->getApiErrorOutput(
					SettingsActiveCampaign::SETTINGS_TYPE_KEY,
					$details,
					$requestBody,
					$this->getErrorMsg($body['errors'] ?? []),
				);
		}
	}

	/**
	 * Check if tag exist by returning tag ID from api.
	 *
	 * @param string $tag Tag name.
	 *
	 * @return string
	 */
	private function getExistingTagId(string $tag): string
	{
		$requestBody = [];

		// Make api request to check if tag exists.
		$response =  \wp_remote_request(
			"{$this->getBaseUrl()}tags",
			[
				'headers' => $this->getHeaders(),
				'method' => 'GET',
			]
		);

		// Output response details.
		$details = $this->getApiReponseDetails($response);
		$code = $details['code'];
		$body = $details['body'];

		// Bailout on wp error.
		if (\is_wp_error($response)) {
			$this->getApiWpErrorOutput(
				SettingsActiveCampaign::SETTINGS_TYPE_KEY,
				$details,
				$requestBody,
				$response->get_error_message()
			);

			return '';
		}

		switch ($code) {
			case 200:
			case 201:
				// Find tag id from array.
				$tagId = \array_filter(
					$body['tags'],
					static function ($item) use ($tag) {
						return $item['tag'] === $tag && $item['tagType'] === 'contact';
					}
				);

				$tagId = \array_values($tagId);

				return $tagId[0]['id'] ?? '';
			default:
				$this->getApiErrorOutput(
					SettingsActiveCampaign::SETTINGS_TYPE_KEY,
					$details,
					$requestBody,
					$this->getErrorMsg($body['errors'] ?? []),
				);

				return '';
		}
	}

	/**
	 * Create a new tag via api.
	 *
	 * @param string $tag Tag name.
	 * @return string
	 */
	private function createNewTag(string $tag): string
	{
		// Prepare body.
		$requestBody = [
			'tag' => [
				'tag' => $tag,
				'tagType' => 'contact',
				'description' => '',
			],
		];

		// Make api request to create a new tag.
		$response = \wp_remote_request(
			"{$this->getBaseUrl()}tags",
			[
				'headers' => $this->getHeaders(),
				'method' => 'POST',
				'body' => \wp_json_encode($requestBody),
			]
		);

		// Output response details.
		$details = $this->getApiReponseDetails($response);
		$code = $details['code'];
		$body = $details['body'];

		// Bailout on wp error.
		if (\is_wp_error($response)) {
			$this->getApiWpErrorOutput(
				SettingsActiveCampaign::SETTINGS_TYPE_KEY,
				$details,
				$requestBody,
				$response->get_error_message()
			);

			return '';
		}

		switch ($code) {
			case 200:
			case 201:
				return $body['id'] ?? '';
			default:
				$this->getApiErrorOutput(
					SettingsActiveCampaign::SETTINGS_TYPE_KEY,
					$details,
					$requestBody,
					$this->getErrorMsg($body['errors'] ?? []),
				);

				return '';
		}
	}

	/**
	 * Map service messages with our own.
	 *
	 * @param array<int, array<string, string>> $errors Additional errors got from the API.
	 *
	 * @return string
	 */
	private function getErrorMsg(array $errors): string
	{
		$msg = '';
		$code = '';

		if ($errors && isset($errors[0])) {
			$code = $errors[0]['code'] ?? '';
			$msg = $errors[0]['error'] ?? '';
		}

		if (!$msg) {
			$msg = $code;
		}

		switch ($msg) {
			case 'contact_email_was_not_provided':
				return 'activeCampaignInvalidEmailError';
			case 'duplicate':
				return 'activeCampaignDuplicateError';
			case 'activeCampaign500':
				return 'activeCampaign500Error';
			case 'activeCampaignForbidden':
				return 'activeCampaignForbiddenError';
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
		// Make api request to get form details.
		$response = \wp_remote_get(
			"{$this->getBaseUrl()}forms/{$listId}",
			[
				'headers' => $this->getHeaders(),
				'timeout' => 60,
			]
		);

		// Output response details.
		$details = $this->getApiReponseDetails($response);
		$body = $details['body'];

		// Bailout if fields are missing.
		if (!isset($body['form']['cfields'])) {
			return [];
		}

		// Prepeare custom actions.
		$actions = [];

		if (isset($body['form']['actiondata']['actions'])) {
			// Map all actions.
			foreach ($body['form']['actiondata']['actions'] as $item) {
				$type = $item['type'] ?? '';

				if (!$type) {
					continue;
				}

				// Map api response with our naming.
				switch ($type) {
					case 'add-a-tag':
						$actions[] = [
							'action' => 'tags',
							'value' => $item['tag'] ?? '',
						];
						break;
					case 'subscribe-to-list':
						$actions[] = [
							'action' => 'lists',
							'value' => $item['list'] ?? '',
						];
						break;
				}
			}
		}

		// Bailout with correct data.
		return [
			'fields' => $body['form']['cfields'],
			'actions' => $actions,
		];
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

		// Output response details.
		$details = $this->getApiReponseDetails($response);
		$body = $details['body'];

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

		$standardFields = \array_flip(ActiveCampaign::STANDARD_FIELDS);

		// Remove unecesery storage field.
		if (isset($params['es-form-storage'])) {
			unset($params['es-form-storage']);
		}

		// Remove unecesery action tags field.
		if (isset($params['actionTags'])) {
			unset($params['actionTags']);
		}

		// Remove unecesery action lists field.
		if (isset($params['actionLists'])) {
			unset($params['actionLists']);
		}

		// Map params.
		foreach ($params as $key => $param) {
			$value = $param['value'] ?? '';

			if (!$value) {
				continue;
			}

			// If standard key use different logic.
			if (isset($standardFields[$key])) {
				// On full name explode first space and output it as first and last name.
				if ($key === 'fullName') {
					$value = \explode(' ', $value, 2);
					$output['firstName'] = $value[0] ?? '';
					$output['lastName'] = $value[1] ?? '';
				} else {
					$output[$key] = $value;
				}
			} else {
				// Mape custom fields.
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
		$url = \rtrim($this->getApiUrl(), '/');

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
