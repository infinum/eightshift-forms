<?php

/**
 * ActiveCampaign Client integration class.
 *
 * @package EightshiftForms\Integrations\ActiveCampaign
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\ActiveCampaign;

use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ActiveCampaign\ActiveCampaignClientInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;

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
		$output = \get_transient(self::CACHE_ACTIVE_CAMPAIGN_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Prevent cache.
		if (UtilsDeveloperHelper::isDeveloperSkipCacheActive()) {
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
	 * @param string $itemId Item id to search.
	 * @param array<string, mixed> $params Params array.
	 * @param array<string, array<int, array<string, mixed>>> $files Files array.
	 * @param string $formId FormId value.
	 *
	 * @return array<string, mixed>
	 */
	public function postApplication(string $itemId, array $params, array $files, string $formId): array
	{
		$params = $this->prepareParams($params, $formId);

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
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsActiveCampaign::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$params,
			$files,
			$itemId,
			$formId,
			UtilsSettingsHelper::isOptionCheckboxChecked(SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_SKIP_INTEGRATION_KEY, SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_SKIP_INTEGRATION_KEY)
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		UtilsDeveloperHelper::setQmLogsOutput($details);

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return UtilsApiHelper::getIntegrationSuccessInternalOutput(
				$details,
				[
					'contactId' => $body['contact']['id'],
				]
			);
		}

		// Filter different error outputs.
		switch ($details['code']) {
			case UtilsConfig::API_RESPONSE_CODE_ERROR_FORBIDDEN:
				$error = 'activeCampaignForbidden';
				break;
			case UtilsConfig::API_RESPONSE_CODE_ERROR_SERVER:
				$error = 'activeCampaign500';
				break;
			default:
				$error = $body['errors'] ?? [];
				break;
		}

		// Output error.
		return UtilsApiHelper::getIntegrationErrorInternalOutput($details);
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
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsActiveCampaign::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$requestBody
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		UtilsDeveloperHelper::setQmLogsOutput($details);

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return UtilsApiHelper::getIntegrationSuccessInternalOutput($details);
		}

		// Output error.
		return UtilsApiHelper::getIntegrationErrorInternalOutput($details);
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
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsActiveCampaign::SETTINGS_TYPE_KEY,
			$response,
			$url
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		UtilsDeveloperHelper::setQmLogsOutput($details);

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return UtilsApiHelper::getIntegrationSuccessInternalOutput($details);
		}

		// Output error.
		return UtilsApiHelper::getIntegrationErrorInternalOutput($details);
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
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsActiveCampaign::SETTINGS_TYPE_KEY,
			$response,
			$url
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		UtilsDeveloperHelper::setQmLogsOutput($details);

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
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
		UtilsApiHelper::getIntegrationErrorInternalOutput($details);

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
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsActiveCampaign::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$requestBody
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		UtilsDeveloperHelper::setQmLogsOutput($details);

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return $body['id'] ?? '';
		}

		// Output error.
		UtilsApiHelper::getIntegrationErrorInternalOutput($details);

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
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsActiveCampaign::SETTINGS_TYPE_KEY,
			$response,
			$url
		);

		$body = $details[UtilsConfig::IARD_BODY];

		UtilsDeveloperHelper::setQmLogsOutput($details);

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
		return UtilsApiHelper::getIntegrationApiReponseDetails(
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

		$body = $details[UtilsConfig::IARD_BODY];

		UtilsDeveloperHelper::setQmLogsOutput($details);

		if (!isset($body['forms'])) {
			return [];
		}

		return $body['forms'];
	}

	/**
	 * Prepare params
	 *
	 * @param array<string, mixed> $params Params.
	 * @param string $formId FormId value.
	 *
	 * @return array<string, mixed>
	 */
	private function prepareParams(array $params, string $formId): array
	{
		$output = [];

		// Map enrichment data.
		$params = $this->enrichment->mapEnrichmentFields($params);

		// Filter params.
		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsActiveCampaign::SETTINGS_TYPE_KEY, 'prePostParams']);
		if (\has_filter($filterName)) {
			$params = \apply_filters($filterName, $params, $formId) ?? [];
		}

		// Remove unecesery params.
		$params = UtilsGeneralHelper::removeUneceseryParamFields($params);

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
				// Mape custom fields.
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
	 * Return Api Key from settings or global vairaible.
	 *
	 * @return string
	 */
	private function getApiKey(): string
	{
		return UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getApiKeyActiveCampaign(), SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_API_KEY_KEY)['value'];
	}

	/**
	 * Return Api Url from settings or global vairaible.
	 *
	 * @return string
	 */
	private function getApiUrl(): string
	{
		return UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getApiUrlActiveCampaign(), SettingsActiveCampaign::SETTINGS_ACTIVE_CAMPAIGN_API_URL_KEY)['value'];
	}
}
