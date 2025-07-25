<?php

/**
 * Mailerlite Client integration class.
 *
 * @package EightshiftForms\Integrations\Mailerlite
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Mailerlite;

use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;

/**
 * MailerliteClient integration class.
 */
class MailerliteClient implements ClientInterface
{
	/**
	 * Return Mailerlite base url.
	 *
	 * @var string
	 */
	private const BASE_URL = 'https://api.mailerlite.com/api/v2/';

	/**
	 * Transient cache name for items.
	 */
	public const CACHE_MAILERLITE_ITEMS_TRANSIENT_NAME = 'es_mailerlite_items_cache';

	/**
	 * Return items.
	 *
	 * @param bool $hideUpdateTime Determin if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getItems(bool $hideUpdateTime = true): array
	{
		$output = \get_transient(self::CACHE_MAILERLITE_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Prevent cache.
		if (UtilsDeveloperHelper::isDeveloperSkipCacheActive()) {
			$output = [];
		}

		// Check if form exists in cache.
		if (!$output) {
			$items = $this->getMailerliteLists();

			if ($items) {
				foreach ($items as $item) {
					$id = $item['id'] ?? '';

					$output[$id] = [
						'id' => (string) $id,
						'title' => $item['name'] ?? '',
						'fields' => [],
					];
				}

				$output[ClientInterface::TRANSIENT_STORED_TIME] = [
					'id' => ClientInterface::TRANSIENT_STORED_TIME,
					'title' => \current_datetime()->format('Y-m-d H:i:s'),
				];

				\set_transient(self::CACHE_MAILERLITE_ITEMS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
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
			$items = $this->getMailerliteListFields();

			if ($items) {
				$output[$itemId]['fields'] = $items;

				\set_transient(self::CACHE_MAILERLITE_ITEMS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
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
		$itemId = $formDetails[UtilsConfig::FD_ITEM_ID];
		$params = $formDetails[UtilsConfig::FD_PARAMS];
		$files = $formDetails[UtilsConfig::FD_FILES];
		$formId = $formDetails[UtilsConfig::FD_FORM_ID];

		// Filter override post request.
		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsMailerlite::SETTINGS_TYPE_KEY, 'overridePostRequest']);
		if (\has_filter($filterName)) {
			$filterValue = \apply_filters($filterName, [], $itemId, $params, $files, $formId) ?? [];

			if ($filterValue) {
				return $filterValue;
			}
		}

		$email = FormsHelper::getParamValue('email', $params);

		$body = [
			'email' => $email,
			'resubscribe' => true,
			'autoresponders' => true,
			'type' => 'active',
			'fields' => $this->prepareParams($params),
		];

		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsMailerlite::SETTINGS_TYPE_KEY, 'prePostId']);
		if (\has_filter($filterName)) {
			$itemId = \apply_filters($filterName, $itemId, $body, $formId) ?? $itemId;
		}

		$url = self::BASE_URL . "groups/{$itemId}/subscribers";

		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode($body),
			]
		);

		// Structure response details.
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsMailerlite::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$body,
			$files,
			$itemId,
			$formId,
			UtilsSettingsHelper::isOptionCheckboxChecked(SettingsMailerlite::SETTINGS_MAILERLITE_SKIP_INTEGRATION_KEY, SettingsMailerlite::SETTINGS_MAILERLITE_SKIP_INTEGRATION_KEY)
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
		$msg = $body['error']['message'] ?? '';

		switch ($msg) {
			case 'Bad Request':
				return 'mailerliteBadRequestError';
			case 'Unauthorized':
				return 'mailerliteErrorSettingsMissing';
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
		$msg = $body['error']['message'] ?? '';

		$output = [];

		switch ($msg) {
			case 'Invalid email address':
				$output['email'] = 'validationEmail';
				break;
			case 'Email temporarily blocked':
				$output['email'] = 'validationEmail';
				break;
		}

		return $output;
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
			'X-MailerLite-ApiKey' => $this->getApiKey(),
		];

		return $headers;
	}

	/**
	 * API request to get one job by ID from Greenhouse.
	 *
	 * @return array<string, mixed>
	 */
	private function getMailerliteListFields()
	{
		$url = self::BASE_URL . "fields";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsMailerlite::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return $body ?? [];
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
		$url = self::BASE_URL . "groups";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		return UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsMailerlite::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);
	}

	/**
	 * API request to get all lists from Mailerlite.
	 *
	 * @return array<string, mixed>
	 */
	private function getMailerliteLists()
	{
		$details = $this->getTestApi();

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return $body ?? [];
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
		// Remove unecesery params.
		$params = UtilsGeneralHelper::removeUneceseryParamFields($params);

		return UtilsGeneralHelper::prepareGenericParamsOutput($params, ['email']);
	}

	/**
	 * Return Api Key from settings or global vairaible.
	 *
	 * @return string
	 */
	private function getApiKey(): string
	{
		return UtilsSettingsHelper::getOptionWithConstant(Variables::getApiKeyMailerlite(), SettingsMailerlite::SETTINGS_MAILERLITE_API_KEY_KEY);
	}
}
