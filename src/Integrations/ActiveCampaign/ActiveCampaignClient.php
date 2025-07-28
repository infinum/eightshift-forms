<?php

/**
 * ActiveCampaign Client integration class.
 *
 * @package EightshiftForms\Integrations\ActiveCampaign
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\ActiveCampaign;

use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ActiveCampaign\ActiveCampaignClientInterface;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\DeveloperHelpers;
use EightshiftForms\Helpers\SettingsHelpers;

/**
 * ActiveCampaignClient integration class.
 */
class ActiveCampaignClient implements ActiveCampaignClientInterface
{
	/**
	 * Transient cache name for items.
	 */
	public const CACHE_ACTIVE_CAMPAIGN_ITEMS_TRANSIENT_NAME = 'es_active_campaign_items_cache';

	/**
	 * Return items.
	 *
	 * @param bool $hideUpdateTime Determine if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getItems(bool $hideUpdateTime = true): array
	{
		$output = \get_transient(self::CACHE_ACTIVE_CAMPAIGN_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Prevent cache.
		if (DeveloperHelpers::isDeveloperSkipCacheActive()) {
			$output = [];
		}

		// Check if form exists in cache.
		if (empty($output)) {
			$items = $this->getActiveCampaignLists();

			if ($items) {
				foreach ($items as $item) {
					$id = $item['id'] ?? '';

					$output[$id] = [
						'id' => $id,
						'title' => $item['name'] ?? '',
						'fields' => [],
					];
				}

				$output[ActiveCampaignClientInterface::TRANSIENT_STORED_TIME] = [
					'id' => ActiveCampaignClientInterface::TRANSIENT_STORED_TIME,
					'title' => \current_datetime()->format('Y-m-d H:i:s'),
				];

				\set_transient(self::CACHE_ACTIVE_CAMPAIGN_ITEMS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
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
		$output = $this->getItems();

		// Check if form exists in cache.
		if (empty($output) || !isset($output[$itemId]) || empty($output[$itemId])) {
			$fields = $this->getActiveCampaignListFields($itemId);

			if ($fields) {
				$output[$itemId]['fields'] = $fields;

				\set_transient(self::CACHE_ACTIVE_CAMPAIGN_ITEMS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
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
		$itemId = $formDetails[Config::FD_ITEM_ID];
		$params = $formDetails[Config::FD_PARAMS];
		$files = $formDetails[Config::FD_FILES];
		$formId = $formDetails[Config::FD_FORM_ID];

		$params = $this->prepareParams($params);

		// Map body.
		$requestBody = [
			'contact' => $params,
		];

		$url = "{$this->getBaseUrl()}contacts";

		// Make an API request.
		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode($requestBody),
			]
		);

		// Structure response details.
		$details = ApiHelpers::getIntegrationApiResponseDetails(
			SettingsActiveCampaign::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$params,
			$files,
			$itemId,
			$formId,
			SettingsHelpers::isOptionCheckboxChecked(SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_SKIP_INTEGRATION_KEY, SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_SKIP_INTEGRATION_KEY)
		);

		$code = $details[Config::IARD_CODE];
		$body = $details[Config::IARD_BODY];

		// On success return output.
		if ($code >= Config::API_RESPONSE_CODE_SUCCESS && $code <= Config::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return ApiHelpers::getIntegrationSuccessInternalOutput(
				$details,
				[
					'contactId' => $body['contact']['id'],
				]
			);
		}

		// Filter different error outputs.
		switch ($details['code']) {
			case Config::API_RESPONSE_CODE_ERROR_FORBIDDEN:
				$error = 'activeCampaignForbidden';
				break;
			case Config::API_RESPONSE_CODE_ERROR_SERVER:
				$error = 'activeCampaign500';
				break;
			default:
				$error = $body['errors'] ?? [];
				break;
		}

		// Output error.
		return ApiHelpers::getIntegrationErrorInternalOutput($details);
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
		$url = "{$this->getBaseUrl()}contactTags";

		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode($requestBody),
			]
		);

		// Structure response details.
		$details = ApiHelpers::getIntegrationApiResponseDetails(
			SettingsActiveCampaign::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$requestBody
		);

		$code = $details[Config::IARD_CODE];

		// On success return output.
		if ($code >= Config::API_RESPONSE_CODE_SUCCESS && $code <= Config::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return ApiHelpers::getIntegrationSuccessInternalOutput($details);
		}

		// Output error.
		return ApiHelpers::getIntegrationErrorInternalOutput($details);
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
		$url = "{$this->getBaseUrl()}contactLists";

		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode($requestBody),
			]
		);

		// Structure response details.
		$details = ApiHelpers::getIntegrationApiResponseDetails(
			SettingsActiveCampaign::SETTINGS_TYPE_KEY,
			$response,
			$url
		);

		$code = $details[Config::IARD_CODE];

		// On success return output.
		if ($code >= Config::API_RESPONSE_CODE_SUCCESS && $code <= Config::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return ApiHelpers::getIntegrationSuccessInternalOutput($details);
		}

		// Output error.
		return ApiHelpers::getIntegrationErrorInternalOutput($details);
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
		$url = "{$this->getBaseUrl()}tags";

		// Make api request to check if tag exists.
		$response =  \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		$details = ApiHelpers::getIntegrationApiResponseDetails(
			SettingsActiveCampaign::SETTINGS_TYPE_KEY,
			$response,
			$url
		);

		$code = $details[Config::IARD_CODE];
		$body = $details[Config::IARD_BODY];

		// On success return output.
		if ($code >= Config::API_RESPONSE_CODE_SUCCESS && $code <= Config::API_RESPONSE_CODE_SUCCESS_RANGE) {
			// Find tag id from array.
			$tagId = \array_filter(
				$body['tags'],
				static function ($item) use ($tag) {
					return $item['tag'] === $tag && $item['tagType'] === 'contact';
				}
			);

			$tagId = \array_values($tagId);

			return $tagId[0]['id'] ?? '';
		}

		// Output error.
		ApiHelpers::getIntegrationErrorInternalOutput($details);

		return '';
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

		$url = "{$this->getBaseUrl()}tags";

		// Make api request to create a new tag.
		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode($requestBody),
			]
		);

		// Structure response details.
		$details = ApiHelpers::getIntegrationApiResponseDetails(
			SettingsActiveCampaign::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$requestBody
		);

		$code = $details[Config::IARD_CODE];
		$body = $details[Config::IARD_BODY];

		// On success return output.
		if ($code >= Config::API_RESPONSE_CODE_SUCCESS && $code <= Config::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return $body['id'] ?? '';
		}

		// Output error.
		ApiHelpers::getIntegrationErrorInternalOutput($details);

		return '';
	}

	/**
	 * Map service messages with our own.
	 *
	 * @param array<mixed> $body API response body.
	 *
	 * @return string
	 */
	private function getErrorMsg(array $body): string // @phpstan-ignore-line
	{
		$msg = '';
		$code = '';

		if (isset($body[0]['code'])) {
			$code = $body[0]['code'] ?? '';
			$msg = $body[0]['error'] ?? '';
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
		$url = "{$this->getBaseUrl()}forms/{$listId}";

		// Make api request to get form details.
		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		$details = ApiHelpers::getIntegrationApiResponseDetails(
			SettingsActiveCampaign::SETTINGS_TYPE_KEY,
			$response,
			$url
		);

		$body = $details[Config::IARD_BODY];

		// Bailout if fields are missing.
		if (!isset($body['form']['cfields'])) {
			return [];
		}

		// Prepare custom actions.
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
	 * Get test api.
	 *
	 * @return array<mixed>
	 */
	public function getTestApi(): array
	{
		$url = "{$this->getBaseUrl()}forms";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		return ApiHelpers::getIntegrationApiResponseDetails(
			SettingsActiveCampaign::SETTINGS_TYPE_KEY,
			$response,
			$url
		);
	}

	/**
	 * API request to get all lists from ActiveCampaign.
	 *
	 * @return array<string, mixed>
	 */
	private function getActiveCampaignLists()
	{
		$details = $this->getTestApi();

		$body = $details[Config::IARD_BODY];

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

		// Remove unnecessary params.
		$params = GeneralHelpers::removeUnnecessaryParamFields($params);

		$standardFields = \array_flip(ActiveCampaign::STANDARD_FIELDS);

		// Map params.
		foreach ($params as $param) {
			$value = $param['value'] ?? '';
			if (!$value) {
				continue;
			}

			$name = $param['name'] ?? '';
			if (!$name) {
				continue;
			}

			if ($name === 'actionTags') {
				continue;
			}

			if ($name === 'actionLists') {
				continue;
			}

			// If standard key use different logic.
			if (isset($standardFields[$name])) {
				// On full name explode first space and output it as first and last name.
				if ($name === 'fullName') {
					$value = \explode(' ', $value, 2);
					$output['firstName'] = $value[0] ?? '';
					$output['lastName'] = $value[1] ?? '';
				} else {
					$output[$name] = $value;
				}
			} else {
				// Map custom fields.
				$output['fieldValues'][] = [
					'field' => $name,
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
	 * Return Api Key from settings or global variable.
	 *
	 * @return string
	 */
	private function getApiKey(): string
	{
		return SettingsHelpers::getOptionWithConstant(Variables::getApiKeyActiveCampaign(), SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_API_KEY_KEY);
	}

	/**
	 * Return Api Url from settings or global variable.
	 *
	 * @return string
	 */
	private function getApiUrl(): string
	{
		return SettingsHelpers::getOptionWithConstant(Variables::getApiUrlActiveCampaign(), SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_API_URL_KEY);
	}
}
