<?php

/**
 * Mailchimp Client integration class.
 *
 * @package EightshiftForms\Integrations\Mailchimp
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailchimp;

use EightshiftForms\Cache\SettingsCache;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;

/**
 * MailchimpClient integration class.
 */
class MailchimpClient implements MailchimpClientInterface
{
	/**
	 * Transient cache name for items.
	 */
	public const CACHE_MAILCHIMP_ITEMS_TRANSIENT_NAME = 'es_mailchimp_items_cache';

	/**
	 * Return items.
	 *
	 * @param bool $hideUpdateTime Determin if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getItems(bool $hideUpdateTime = true): array
	{
		$output = \get_transient(self::CACHE_MAILCHIMP_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Prevent cache.
		if (UtilsDeveloperHelper::isDeveloperSkipCacheActive()) {
			$output = [];
		}

		// Check if form exists in cache.
		if (empty($output)) {
			$items = $this->getMailchimpLists();

			if ($items) {
				foreach ($items as $item) {
					$id = $item['id'] ?? '';

					$output[$id] = [
						'id' => $id,
						'title' => $item['name'] ?? '',
						'fields' => [],
						'tags' => [],
					];
				}

				$output[ClientInterface::TRANSIENT_STORED_TIME] = [
					'id' => ClientInterface::TRANSIENT_STORED_TIME,
					'title' => \current_datetime()->format('Y-m-d H:i:s'),
				];

				\set_transient(self::CACHE_MAILCHIMP_ITEMS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
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
			$items = $this->getMailchimpListFields($itemId);

			if ($items) {
				$output[$itemId]['fields'] = $items;

				\set_transient(self::CACHE_MAILCHIMP_ITEMS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
			}
		}

		return $output[$itemId] ?? [];
	}

	/**
	 * Return tags with cache option for faster loading.
	 *
	 * @param string $itemId Item id to search.
	 *
	 * @return array<int, mixed>
	 */
	public function getTags(string $itemId): array
	{
		$output = $this->getItems();

		$item = $output[$itemId]['tags'] ?? [];

		// Check if form exists in cache.
		if (!$output || !$item) {
			$items = $this->getMailchimpTags($itemId);

			if ($items) {
				$output[$itemId]['tags'] = \array_map(
					static function ($item) {
						return [
							'id' => (string) $item['id'],
							'name' => (string) $item['name'],
						];
					},
					$items
				);

				\set_transient(self::CACHE_MAILCHIMP_ITEMS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
			}
		}

		return $output[$itemId]['tags'] ?? [];
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
		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsMailchimp::SETTINGS_TYPE_KEY, 'overridePostRequest']);
		if (\has_filter($filterName)) {
			$filterValue = \apply_filters($filterName, [], $itemId, $params, $files, $formId) ?? [];

			if ($filterValue) {
				return $filterValue;
			}
		}

		$email = UtilsGeneralHelper::getEmailParamsField($params);
		$emailHash = \md5(\strtolower($email));
		$preparedParams = $this->prepareParams($params);
		$tags = $this->prepareTags($params);

		$body = [
			'email_address' => $email,
			'status_if_new' => 'subscribed',
			'status' => 'subscribed',
		];

		// If there are any merge_fields, add them to the body as empty will throw an error.
		if ($preparedParams) {
			$body['merge_fields'] = $preparedParams;
		}

		// If there are any tags, add them to the body as empty will throw an error.
		if ($tags) {
			$body['tags'] = $tags;
		}

		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsMailchimp::SETTINGS_TYPE_KEY, 'prePostId']);
		if (\has_filter($filterName)) {
			$itemId = \apply_filters($filterName, $itemId, $body, $formId) ?? $itemId;
		}

		$url = "{$this->getBaseUrl()}lists/{$itemId}/members/{$emailHash}";

		$response = \wp_remote_request(
			$url,
			[
				'headers' => $this->getHeaders(),
				'method' => 'PUT',
				'body' => \wp_json_encode($body),
			]
		);

		// Structure response details.
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsMailchimp::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$body,
			$files,
			$itemId,
			$formId,
			UtilsSettingsHelper::isOptionCheckboxChecked(SettingsMailchimp::SETTINGS_MAILCHIMP_SKIP_INTEGRATION_KEY, SettingsMailchimp::SETTINGS_MAILCHIMP_SKIP_INTEGRATION_KEY)
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
		$msg = $body['detail'] ?? '';

		switch ($msg) {
			case 'Bad Request':
				return 'mailchimpBadRequestError';
			case 'Your request did not include an API key.':
				return 'mailchimpErrorSettingsMissing';
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
		$msg = $body['detail'] ?? '';
		$errors = $body['errors'] ?? [];

		$output = [];

		foreach ($errors as $value) {
			$key = $value['field'] ?? '';
			$message = $value['message'] ?? '';

			if (!$key || !$message) {
				continue;
			}

			switch ($message) {
				case 'This value should not be blank.':
					$output[$key] = 'validationRequired';
					break;
				case 'That is not a valid URL':
					$output[$key] = 'validationUrl';
					break;
				case 'Please enter a zip code (5 digits)':
					$output[$key] = 'validationMailchimpInvalidZip';
					break;
				case 'Please enter a month (01-12) and a day (01-31)':
				case 'Please enter the date':
					$output[$key] = 'validationDate';
					break;
			}
		}

		if ($msg === 'Please provide a valid email address.') {
			$output['email_address'] = 'validationEmail';
		}

		return $output;
	}

	/**
	 * Return Mailchimp tags for a list.
	 *
	 * @param string $itemId Item id to search.
	 *
	 * @return array<int, mixed>
	 */
	private function getMailchimpTags(string $itemId): array
	{
		$url = "{$this->getBaseUrl()}lists/{$itemId}/tag-search";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsMailchimp::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return $body['tags'] ?? [];
		}

		return [];
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
			'Authorization' => "Bearer {$this->getApiKey()}"
		];

		return $headers;
	}

	/**
	 * API request to get one job by ID from Mailchimp.
	 *
	 * @param string $listId List id to search.
	 *
	 * @return array<string, mixed>
	 */
	private function getMailchimpListFields(string $listId)
	{
		$url = "{$this->getBaseUrl()}lists/{$listId}/merge-fields?count=1000";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsMailchimp::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return $body['merge_fields'] ?? [];
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
		$url = "{$this->getBaseUrl()}lists?count=1";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		return UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsMailchimp::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);
	}

	/**
	 * API request to get all lists from Mailchimp.
	 *
	 * @return array<string, mixed>
	 */
	private function getMailchimpLists()
	{
		$url = "{$this->getBaseUrl()}lists?count=1000";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsMailchimp::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return $body['lists'] ?? [];
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

		// Remove unecesery params.
		$params = UtilsGeneralHelper::removeUneceseryParamFields($params);

		foreach ($params as $param) {
			$value = $param['value'] ?? '';
			if (!$value) {
				continue;
			}

			$name = $param['name'] ?? '';
			if (!$name) {
				continue;
			}

			if ($name === 'email_address') {
				continue;
			}

			// Check for custom address.
			if ($name === 'ADDRESS') {
				$output[$name] = [
					'addr1' => $value,
					'addr2' => '',
					'city' => '&sbsp;',
					'state' => '',
					'zip' => '&sbsp;',
					'country' => '',
				];

				continue;
			}

			$output[$name] = $value;
		}

		return $output;
	}

	/**
	 * Prepare tags
	 *
	 * @param array<string, mixed> $params Params.
	 *
	 * @return array<int, string>
	 */
	private function prepareTags(array $params): array
	{
		$key = UtilsHelper::getStateParam('mailchimpTags');

		if (!isset($params[$key])) {
			return [];
		}

		$value = $params[$key]['value'] ?? '';

		if (!$value) {
			return [];
		}

		return $value;
	}

	/**
	 * Return Mailchimp base url.
	 *
	 * @return string
	 */
	private function getBaseUrl(): string
	{
		$key = \explode('-', $this->getApiKey());
		$server = \end($key);

		return "https://{$server}.api.mailchimp.com/3.0/";
	}

	/**
	 * Return Api Key from settings or global vairaible.
	 *
	 * @return string
	 */
	private function getApiKey(): string
	{
		return UtilsSettingsHelper::getOptionWithConstant(Variables::getApiKeyMailchimp(), SettingsMailchimp::SETTINGS_MAILCHIMP_API_KEY_KEY);
	}
}
