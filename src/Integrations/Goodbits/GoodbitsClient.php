<?php

/**
 * Goodbits Client integration class.
 *
 * @package EightshiftForms\Integrations\Goodbits
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Goodbits;

use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

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
		$key = $this->getApiKey();

		if (\is_string($key) && Components::isJson($key)) {
			$key = \json_decode($key);

			$output = [];

			foreach ($key as $itemKey => $itemValue) {
				$output[(string) $itemValue] = [
					'title' => $itemKey,
					'id' => $itemValue,
				];
			}

			return $output;
		}

		return [
			'Goodbits' => [
				'title' => \__('Goodbits', 'eightshift-forms'),
				'id' => $key,
			],
		];
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
	 * @param string $itemId Item id to search.
	 * @param array<string, mixed> $params Params array.
	 * @param array<string, array<int, array<string, mixed>>> $files Files array.
	 * @param string $formId FormId value.
	 *
	 * @return array<string, mixed>
	 */
	public function postApplication(string $itemId, array $params, array $files, string $formId): array
	{
		$body = [
			'subscriber' => $this->prepareParams($params, $formId),
		];

		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsGoodbits::SETTINGS_TYPE_KEY, 'prePostId']);
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
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsGoodbits::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$body,
			$files,
			$itemId,
			$formId,
			UtilsSettingsHelper::isOptionCheckboxChecked(SettingsGoodbits::SETTINGS_GOODBITS_SKIP_INTEGRATION_KEY, SettingsGoodbits::SETTINGS_GOODBITS_SKIP_INTEGRATION_KEY)
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		UtilsDeveloperHelper::setQmLogsOutput($details);

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
		return UtilsApiHelper::getIntegrationApiReponseDetails(
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
				return 'goodbitsBadRequestError';
			case 'Invalid API Key has been submitted, please refer to your API key under your settings':
				return 'goodbitsErrorSettingsMissing';
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
	 * @param string $formId FormId value.
	 *
	 * @return array<string, mixed>
	 */
	private function prepareParams(array $params, string $formId): array
	{
		// Map enrichment data.
		$params = $this->enrichment->mapEnrichmentFields($params);

		// Filter params.
		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsGoodbits::SETTINGS_TYPE_KEY, 'prePostParams']);
		if (\has_filter($filterName)) {
			$params = \apply_filters($filterName, $params, $formId) ?? [];
		}

		// Remove unecesery params.
		$params = UtilsGeneralHelper::removeUneceseryParamFields($params);

		return UtilsGeneralHelper::prepareGenericParamsOutput($params);
	}

	/**
	 * Return Api Key from settings or global variable.
	 *
	 * @return string
	 */
	private function getApiKey(): string
	{
		return UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getApiKeyGoodbits(), SettingsGoodbits::SETTINGS_GOODBITS_API_KEY_KEY)['value'];
	}
}
