<?php

/**
 * Moments Client integration class.
 *
 * @package EightshiftForms\Integrations\Moments
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Moments;

use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Rest\ApiHelper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

/**
 * MomentsClient integration class.
 */
class MomentsClient implements ClientInterface
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
	public const CACHE_MOMENTS_ITEMS_TRANSIENT_NAME = 'es_moments_items_cache';

	/**
	 * Transient cache name for IBSSO Token.
	 */
	public const CACHE_MOMENTS_TOKEN_TRANSIENT_NAME = 'es_moments_token_cache';

	/**
	 * Return items.
	 *
	 * @param bool $hideUpdateTime Determin if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getItems(bool $hideUpdateTime = true): array
	{
		$output = \get_transient(self::CACHE_MOMENTS_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Check if form exists in cache.
		if (empty($output)) {
			$items = $this->getMomentsLists();

			if ($items) {
				foreach ($items as $item) {
					$id = $item['id'] ?? '';

					$output[$id] = [
						'id' => (string) $id,
						'title' => $item['name'] ?? '',
						'fields' => $item['elements'] ?? [],
					];
				}

				$output[ClientInterface::TRANSIENT_STORED_TIME] = [
					'id' => ClientInterface::TRANSIENT_STORED_TIME,
					'title' => \current_datetime()->format('Y-m-d H:i:s'),
				];

				\set_transient(self::CACHE_MOMENTS_ITEMS_TRANSIENT_NAME, $output, 3600);
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
		$output = \get_transient(self::CACHE_MOMENTS_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Check if form exists in cache.
		if (empty($output) || !isset($output[$itemId]) || empty($output[$itemId])) {
			$output = $this->getItems();
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
		$body = $this->prepareParams($params);

		$url = "{$this->getBaseUrl()}forms/1/forms/{$itemId}/data";

		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders('api'),
				'body' => \wp_json_encode($body),
			]
		);

		// Structure response details.
		$details = $this->getApiReponseDetails(
			SettingsMoments::SETTINGS_TYPE_KEY,
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
		$msg = $body['error']['message'] ?? '';

		switch ($msg) {
			case 'Bad Request':
				return 'momentsBadRequestError';
			case 'Invalid email address':
				return 'momentsInvalidEmailError';
			case 'Email temporarily blocked':
				return 'momentsEmailTemporarilyBlockedError';
			default:
				return 'submitWpError';
		}
	}

	/**
	 * Set headers used for fetching data.
	 *
	 * @param string $isPostType Type of post. Options: default, api, ibsso.
	 * @param string $token Auth token.
	 *
	 * @return array<string, mixed>
	 */
	private function getHeaders(string $isPostType = 'default', string $token = ''): array
	{
		$output = [
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
		];

		if ($isPostType === 'api') {
			$output['Authorization'] = "App {$this->getApiKey()}";
		}

		if ($isPostType === 'ibsso') {
			$output['Authorization'] = "IBSSO {$token}";
		}

		return $output;
	}

	/**
	 * API request to get all lists from Moments.
	 *
	 * @return array<string, mixed>
	 */
	private function getMomentsLists()
	{
		$token = $this->getIbssoToken();

		$url = "{$this->getBaseUrl()}/forms/1/forms?limit=100";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders('ibsso', $token),
			]
		);

		// Structure response details.
		$details = $this->getApiReponseDetails(
			SettingsMoments::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details['code'];
		$body = $details['body'];

		// On success return output.
		if ($code >= 200 && $code <= 299) {
			return $body['forms'] ?? [];
		}

		return [];
	}

	/**
	 * Create and api call to get a IBSSO token or get it from transient.
	 *
	 * @return string
	 */
	private function getIbssoToken(): string
	{
		$token = \get_transient(self::CACHE_MOMENTS_TOKEN_TRANSIENT_NAME); // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		if ($token) {
			return $token;
		}

		$url = "{$this->getBaseUrl()}auth/1/session";

		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode([
					'username' => $this->getApiUsername(),
					'password' => $this->getApiPassword(),
					'unsafe' => false,
				]),
			]
		);

		// Structure response details.
		$details = $this->getApiReponseDetails(
			SettingsMoments::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details['code'];
		$body = $details['body'];

		// On success return output.
		if ($code >= 200 && $code <= 299) {
			$token = $body['token'] ?? '';

			if (!$token) {
				return '';
			}

			\set_transient(self::CACHE_MOMENTS_TOKEN_TRANSIENT_NAME, $token, \HOUR_IN_SECONDS - 60);
			return $token;
		}

		return '';
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

			$output[$key] = $param['value'] ?? '';
		}

		return $output;
	}

	/**
	 * Return Moments base url.
	 *
	 * @return string
	 */
	private function getBaseUrl(): string
	{
		$url = \rtrim($this->getApiUrl(), '/');

		return "{$url}/";
	}

	/**
	 * Return Api Key from settings or global vairaible.
	 *
	 * @return string
	 */
	private function getApiKey(): string
	{
		$apiKey = Variables::getApiKeyMoments();

		return !empty($apiKey) ? $apiKey : $this->getOptionValue(SettingsMoments::SETTINGS_MOMENTS_API_KEY_KEY);
	}

	/**
	 * Return Api Url from settings or global vairaible.
	 *
	 * @return string
	 */
	private function getApiUrl(): string
	{
		$apiUrl = Variables::getApiUrlMoments();

		return !empty($apiUrl) ? $apiUrl : $this->getOptionValue(SettingsMoments::SETTINGS_MOMENTS_API_URL_KEY);
	}

	/**
	 * Return Api Username from settings or global vairaible.
	 *
	 * @return string
	 */
	private function getApiUsername(): string
	{
		$apiUsername = Variables::getApiUsernameMoments();

		return !empty($apiUsername) ? $apiUsername : $this->getOptionValue(SettingsMoments::SETTINGS_MOMENTS_API_USERNAME_KEY);
	}

	/**
	 * Return Api Password from settings or global vairaible.
	 *
	 * @return string
	 */
	private function getApiPassword(): string
	{
		$apiPAssword = Variables::getApiPasswordMoments();

		return !empty($apiPAssword) ? $apiPAssword : $this->getOptionValue(SettingsMoments::SETTINGS_MOMENTS_API_PASSWORD_KEY);
	}
}
