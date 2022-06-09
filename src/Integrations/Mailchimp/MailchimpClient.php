<?php

/**
 * Mailchimp Client integration class.
 *
 * @package EightshiftForms\Integrations\Mailchimp
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailchimp;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Settings\SettingsHelper;

/**
 * MailchimpClient integration class.
 */
class MailchimpClient implements MailchimpClientInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Transient cache name for items.
	 */
	public const CACHE_MAILCHIMP_ITEMS_TRANSIENT_NAME = 'es_mailchimp_items_cache';

	/**
	 * Transient cache name for item.
	 */
	public const CACHE_MAILCHIMP_ITEM_TRANSIENT_NAME = 'es_mailchimp_item_cache';

	/**
	 * Transient cache name for item tags.
	 */
	public const CACHE_MAILCHIMP_ITEM_TAGS_TRANSIENT_NAME = 'es_mailchimp_item_tags_cache';

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

		// Check if form exists in cache.
		if (empty($output)) {
			$items = $this->getMailchimpLists();

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

				\set_transient(self::CACHE_MAILCHIMP_ITEMS_TRANSIENT_NAME, $output, 3600);
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
		$output = \get_transient(self::CACHE_MAILCHIMP_ITEM_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Check if form exists in cache.
		if (empty($output) || !isset($output[$itemId]) || empty($output[$itemId])) {
			$fields = $this->getMailchimpListFields($itemId);

			if ($itemId && $fields) {
				$output[$itemId] = $fields;

				\set_transient(self::CACHE_MAILCHIMP_ITEM_TRANSIENT_NAME, $output, 3600);
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
		$output = \get_transient(self::CACHE_MAILCHIMP_ITEM_TAGS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Check if form exists in cache.
		if (empty($output) || !isset($output[$itemId]) || empty($output[$itemId])) {
			$tags = $this->getMailchimpTags($itemId);

			if ($tags) {
				$output[$itemId] = \array_map(
					static function ($item) {
						return [
							'id' => (string) $item['id'],
							'name' => (string) $item['name'],
						];
					},
					$tags
				);

				\set_transient(self::CACHE_MAILCHIMP_ITEM_TAGS_TRANSIENT_NAME, $output, 3600);
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
		$email = $params['email_address']['value'];
		$emailHash = \md5(\strtolower($email));
		$prepareParams = $this->prepareParams($params);

		$body = [
			'email_address' => $email,
			'status_if_new' => 'subscribed',
			'status' => 'subscribed',
			'tags' => $this->prepareTags($params),
		];

		if (!empty($prepareParams)) {
			$body['merge_fields'] = $prepareParams;
		}

		$response = \wp_remote_request(
			"{$this->getBaseUrl()}lists/{$itemId}/members/{$emailHash}",
			[
				'headers' => $this->getHeaders(),
				'method' => 'PUT',
				'body' => \wp_json_encode($body),
			]
		);

		if (\is_wp_error($response)) {
			return [
				'status' => 'error',
				'code' => 400,
				'message' => $this->getErrorMsg('submitWpError'),
			];
		}

		$code = $response['response']['code'] ? $response['response']['code'] : 200;

		if ($code === 200) {
			return [
				'status' => 'success',
				'code' => $code,
				'message' => 'mailchimpSuccess',
			];
		}

		$responseBody = \json_decode(\wp_remote_retrieve_body($response), true);
		$responseMessage = $responseBody['detail'] ?? '';
		$responseErrors = $responseBody['errors'] ?? [];

		$output = [
			'status' => 'error',
			'code' => $code,
			'message' => $this->getErrorMsg($responseMessage, $responseErrors),
		];

		Helper::logger([
			'integration' => 'mailchimp',
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
		if ($errors) {
			$invalidEmail = \array_filter(
				$errors,
				static function ($error) {
					return $error['field'] === 'email_address';
				}
			);

			if ($invalidEmail) {
				$msg = 'INVALID_EMAIL';
			}
		}

		switch ($msg) {
			case 'Bad Request':
				return 'mailchimpBadRequestError';
			case "The resource submitted could not be validated. For field-specific details, see the 'errors' array.":
				return 'mailchimpInvalidResourceError';
			case 'INVALID_EMAIL':
			case 'Please provide a valid email address.':
				return 'mailchimpInvalidEmailError';
			case 'Your merge fields were invalid.':
				return 'mailchimpMissingFieldsError';
			default:
				return 'submitWpError';
		}
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
		$response = \wp_remote_get(
			"{$this->getBaseUrl()}lists/{$itemId}/tag-search",
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
		$response = \wp_remote_get(
			"{$this->getBaseUrl()}lists/{$listId}/merge-fields?count=1000",
			[
				'headers' => $this->getHeaders(),
				'timeout' => 60,
			]
		);

		$body = \json_decode(\wp_remote_retrieve_body($response), true);

		if (!isset($body['merge_fields'])) {
			return [];
		}

		return $body['merge_fields'];
	}

	/**
	 * API request to get all lists from Mailchimp.
	 *
	 * @return array<string, mixed>
	 */
	private function getMailchimpLists()
	{
		$response = \wp_remote_get(
			"{$this->getBaseUrl()}lists?count=100",
			[
				'headers' => $this->getHeaders(),
				'timeout' => 60,
			]
		);

		$body = \json_decode(\wp_remote_retrieve_body($response), true);

		if (!isset($body['lists'])) {
			return [];
		}

		return $body['lists'];
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

		unset($params['email_address']);
		unset($params[Mailchimp::FIELD_MAILCHIMP_TAGS_KEY]);

		foreach ($params as $key => $param) {
			$value = $param['value'] ?? '';

			switch ($param['name']) {
				case 'address':
					if ($value) {
						$output[$key] = [
							'addr1' => $value,
							'addr2' => '',
							'city' => '&sbsp;',
							'state' => '',
							'zip' => '&sbsp;',
							'country' => '',
						];
					}
					break;
				default:
					$output[$key] = $value;
					break;
			}
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
		$key = Mailchimp::FIELD_MAILCHIMP_TAGS_KEY;

		if (!isset($params[$key])) {
			return [];
		}

		$value = $params[$key]['value'];

		if (empty($value)) {
			return [];
		}

		return \explode(', ', $params[$key]['value']);
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
		$apiKey = Variables::getApiKeyMailchimp();

		return !empty($apiKey) ? $apiKey : $this->getOptionValue(SettingsMailchimp::SETTINGS_MAILCHIMP_API_KEY_KEY);
	}
}
