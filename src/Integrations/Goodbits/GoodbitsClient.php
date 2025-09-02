<?php

/**
 * Goodbits Client integration class.
 *
 * @package EightshiftForms\Integrations\Goodbits
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Goodbits;

use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\HooksHelpers;
use EightshiftForms\Troubleshooting\SettingsFallback;
use Exception;

/**
 * GoodbitsClient integration class.
 */
class GoodbitsClient implements ClientInterface
{
	/**
	 * Return Goodbits base url.
	 *
	 * @var string
	 */
	private const BASE_URL = 'https://app.goodbits.io/api/v1/';

	/**
	 * Return items.
	 *
	 * @param bool $hideUpdateTime Determine if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getItems(bool $hideUpdateTime = true): array
	{
		$key = $this->getApiKey();

		try {
			$output = [];

			$key = \json_decode($key);

			foreach ($key as $itemKey => $itemValue) {
				$output[(string) $itemValue] = [
					'title' => $itemKey,
					'id' => $itemValue,
				];
			}

			return $output;
		} catch (Exception $e) {
			return [
				'Goodbits' => [
					'title' => \__('Goodbits', 'eightshift-forms'),
					'id' => $key,
				],
			];
		}
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
		return $this->getItems()[$itemId] ?? [];
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

		// Filter override post request.
		$filterName = HooksHelpers::getFilterName(['integrations', SettingsGoodbits::SETTINGS_TYPE_KEY, 'overridePostRequest']);
		if (\has_filter($filterName)) {
			$filterValue = \apply_filters($filterName, [], $itemId, $params, $files, $formId) ?? [];

			if ($filterValue) {
				return $filterValue;
			}
		}

		$body = [
			'subscriber' => $this->prepareParams($params),
		];

		$filterName = HooksHelpers::getFilterName(['integrations', SettingsGoodbits::SETTINGS_TYPE_KEY, 'prePostId']);
		if (\has_filter($filterName)) {
			$itemId = \apply_filters($filterName, $itemId, $body, $formId) ?? $itemId;
		}

		$url = self::BASE_URL . "subscribers";

		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders($itemId),
				'body' => \wp_json_encode($body),
			]
		);

		// Structure response details.
		$details = ApiHelpers::getIntegrationApiResponseDetails(
			SettingsGoodbits::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$body,
			$files,
			$itemId,
			$formId
		);

		$code = $details[Config::IARD_CODE];
		$body = $details[Config::IARD_BODY];

		// On success return output.
		if (ApiHelpers::isSuccessResponse($code)) {
			return ApiHelpers::getIntegrationSuccessInternalOutput($details);
		}

		$details[Config::IARD_VALIDATION] = $this->getFieldsErrors($body);
		$details[Config::IARD_MSG] = $this->getErrorMsg($body);

		// Output error.
		return ApiHelpers::getIntegrationErrorInternalOutput($details);
	}

	/**
	 * Get test api.
	 *
	 * @return array<mixed>
	 */
	public function getTestApi(): array
	{
		$url = self::BASE_URL . "newsletter";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders($this->getItems()[0]['id'] ?? ''),
			]
		);

		// Structure response details.
		return ApiHelpers::getIntegrationApiResponseDetails(
			SettingsGoodbits::SETTINGS_TYPE_KEY,
			$response,
			$url,
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
		$msg = $body['error'] ?? '';

		if (!$msg) {
			$msg = !\is_array($body['errors']) ? $body['errors'] : '';
		}

		switch ($msg) {
			case 'Bad Request':
				return SettingsFallback::SETTINGS_FALLBACK_FLAG_GOODBITS_BAD_REQUEST_ERROR;
			case 'Invalid API Key has been submitted, please refer to your API key under your settings':
				return SettingsFallback::SETTINGS_FALLBACK_FLAG_GOODBITS_MISSING_CONFIG;
			default:
				return SettingsFallback::SETTINGS_FALLBACK_FLAG_SUBMIT_INTEGRATION_ERROR_WP;
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
		$errors = $body['errors']['message'] ?? [];

		$output = [];

		if (!$errors) {
			return $output;
		}

		foreach ($errors as $value) {
			switch ($value) {
				case 'Email is invalid':
					$output['email'] = 'validationEmail';
					break;
			}
		}

		return $output;
	}

	/**
	 * Set headers used for fetching data.
	 *
	 * @param string $itemId Name of the api key.
	 *
	 * @return array<string, mixed>
	 */
	private function getHeaders(string $itemId): array
	{
		$headers = [
			'Content-Type' => 'application/json; charset=utf-8',
			'Authorization' => $itemId,
		];

		return $headers;
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
		// Remove unnecessary params.
		$params = GeneralHelpers::removeUnnecessaryParamFields($params);

		return GeneralHelpers::prepareGenericParamsOutput($params);
	}

	/**
	 * Return Api Key from settings or global variable.
	 *
	 * @return string
	 */
	private function getApiKey(): string
	{
		return SettingsHelpers::getOptionWithConstant(Variables::getApiKeyGoodbits(), SettingsGoodbits::SETTINGS_GOODBITS_API_KEY_KEY);
	}
}
