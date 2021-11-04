<?php

/**
 * Mailchimp Client integration class.
 *
 * @package EightshiftForms\Integrations\Mailchimp
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailchimp;

use EightshiftForms\Hooks\Variables;
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
	 * Transient cache name for list.
	 */
	public const CACHE_MAILCHIMP_LISTS_TRANSIENT_NAME = 'es_mailchimp_lists_cache';

	/**
	 * Transient cache name for list fields.
	 */
	public const CACHE_MAILCHIMP_LIST_FIELDS_TRANSIENT_NAME = 'es_mailchimp_list_fields_cache';

	/**
	 * Get Mailchimp lists with cache.
	 *
	 * @return array<string, mixed>
	 */
	public function getLists(): array
	{
		$output = get_transient(self::CACHE_MAILCHIMP_LISTS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		if (empty($output)) {
			$lists = $this->getMailchimpLists();

			if ($lists) {
				foreach ($lists as $job) {
					$id = $job['id'] ?? '';

					$output[$id] = [
						'id' => $id,
						'title' => $job['name'] ?? '',
					];
				}

				set_transient(self::CACHE_MAILCHIMP_LISTS_TRANSIENT_NAME, $output, 3600);
			}
		}

		return $output;
	}

	/**
	 * Return list fields with cache option for faster loading.
	 *
	 * @param string $formId Form Id.
	 *
	 * @return array<string, mixed>
	 */
	public function getListFields(string $formId): array
	{
		$output = get_transient(self::CACHE_MAILCHIMP_LIST_FIELDS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Check if form exists in cache.
		if (empty($output) || !isset($output[$formId]) || empty($output[$formId])) {
			$fields = $this->getMailchimpListFields($formId);

			if ($formId && $fields) {
				$output[$formId] = $fields ?? [];

				set_transient(self::CACHE_MAILCHIMP_LIST_FIELDS_TRANSIENT_NAME, $output, 3600);
			}
		}

		return $output[$formId] ?? [];
	}

	/**
	 * API request to post mailchimp subscription to Mailchimp.
	 *
	 * @param string $listId List id.
	 * @param array<string, mixed> $params Params array.
	 *
	 * @return array<string, mixed>
	 */
	public function postMailchimpSubscription(string $listId, array $params): array
	{
		$email = $params['email_address']['value'];
		$emailHash = md5(strtolower($email));

		$response = \wp_remote_request(
			"{$this->getApiUrl()}lists/{$listId}/members/{$emailHash}",
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

		error_log( print_r( ( json_decode(\wp_remote_retrieve_body($response), true) ), true ) );

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
			$output[$key] = $value['value'] ?? '';
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
