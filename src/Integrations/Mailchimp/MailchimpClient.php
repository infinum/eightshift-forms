<?php

/**
 * Mailchimp Client integration class.
 *
 * @package EightshiftForms\Integrations\Mailchimp
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailchimp;

use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Settings\SettingsHelper;

/**
 * MailchimpClient integration class.
 */
class MailchimpClient implements ClientInterface
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
	 * Return items.
	 *
	 * @return array<string, mixed>
	 */
	public function getItems(): array
	{
		$output = get_transient(self::CACHE_MAILCHIMP_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

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

				set_transient(self::CACHE_MAILCHIMP_ITEMS_TRANSIENT_NAME, $output, 3600);
			}
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
		$output = get_transient(self::CACHE_MAILCHIMP_ITEM_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Check if form exists in cache.
		if (empty($output) || !isset($output[$itemId]) || empty($output[$itemId])) {
			$fields = $this->getMailchimpListFields($itemId);

			if ($itemId && $fields) {
				$output[$itemId] = $fields ?? [];

				set_transient(self::CACHE_MAILCHIMP_ITEM_TRANSIENT_NAME, $output, 3600);
			}
		}

		return $output[$itemId] ?? [];
	}

	/**
	 * API request to post application.
	 *
	 * @param string $itemId Item id to search.
	 * @param array<string, mixed>  $params Params array.
	 * @param array<string, mixed>  $files Files array.
	 *
	 * @return array<string, mixed>
	 */
	public function postApplication(string $itemId, array $params, array $files): array
	{
		$email = $params['email_address']['value'];
		$emailHash = md5(strtolower($email));

		$response = \wp_remote_request(
			"{$this->getApiUrl()}lists/{$itemId}/members/{$emailHash}",
			[
				'headers' => $this->getHeaders(),
				'method' => 'PUT',
				'body' => wp_json_encode(
					[
						'email_address' => $email,
						'status_if_new' => 'subscribed',
						'status' => 'subscribed',
						'merge_fields' => $this->prepareParams($params)
					]
				),
			]
		);

		return json_decode(\wp_remote_retrieve_body($response), true) ?? [];
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
	 * API request to get one job by ID from Greenhouse.
	 *
	 * @param string $listId List id to search.
	 *
	 * @return array<string, mixed>
	 */
	private function getMailchimpListFields(string $listId)
	{
		$response = \wp_remote_get(
			"{$this->getApiUrl()}lists/{$listId}/merge-fields",
			[
				'headers' => $this->getHeaders(),
				'timeout' => 60,
			]
		);

		$body = json_decode(\wp_remote_retrieve_body($response), true);

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
			"{$this->getApiUrl()}lists",
			[
				'headers' => $this->getHeaders(),
				'timeout' => 60,
			]
		);

		$body = json_decode(\wp_remote_retrieve_body($response), true);

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
	 * @return object
	 */
	private function prepareParams(array $params): object
	{
		$output = [];

		unset($params['email_address']);

		foreach ($params as $key => $value) {
			switch ($value['name']) {
				case 'address':
					$output[$key] = [
						'addr1' => $value['value'],
						'addr2' => '',
						'city' => '&sbsp;',
						'state' => '',
						'zip' => '&sbsp;',
						'country' => '',
					];
					break;
				default:
					$output[$key] = $value['value'] ?? '';
					break;
			}
		}

		return (object) $output;
	}

	/**
	 * Return API url.
	 *
	 * @return string
	 */
	private function getApiUrl(): string
	{
		$key = explode('-', $this->getApiKey());
		$server = end($key);

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
