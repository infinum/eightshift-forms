<?php

/**
 * Goodbits Client integration class.
 *
 * @package EightshiftForms\Integrations\Goodbits
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Goodbits;

use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Rest\ApiHelper;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Validation\Validator;
use EightshiftFormsVendor\EightshiftLibs\Helpers\ObjectHelperTrait;

/**
 * GoodbitsClient integration class.
 */
class GoodbitsClient implements ClientInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Helper trait.
	 */
	use ObjectHelperTrait;

	/**
	 * Use API helper trait.
	 */
	use ApiHelper;

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

		if (\is_string($key) && $this->isJson($key)) {
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
			'subscriber' => $this->prepareParams($params),
		];

		$url = self::BASE_URL . "subscribers";

		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders($itemId),
				'body' => \wp_json_encode($body),
			]
		);

		// Structure response details.
		$details = $this->getIntegrationApiReponseDetails(
			SettingsGoodbits::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$body,
			$files,
			$itemId,
			$formId,
			$this->isOptionCheckboxChecked(SettingsGoodbits::SETTINGS_GOODBITS_SKIP_INTEGRATION_KEY, SettingsGoodbits::SETTINGS_GOODBITS_SKIP_INTEGRATION_KEY)
		);

		$code = $details['code'];
		$body = $details['body'];

		Helper::setQmLogsOutput($details);

		// On success return output.
		if ($code >= 200 && $code <= 299) {
			return $this->getIntegrationApiSuccessOutput($details);
		}

		// Output error.
		return $this->getIntegrationApiErrorOutput(
			$details,
			$this->getErrorMsg($body),
			[
				Validator::VALIDATOR_OUTPUT_KEY => $this->getFieldsErrors($body),
			]
		);
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
		return $this->getIntegrationApiReponseDetails(
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
	 *
	 * @return array<string, mixed>
	 */
	private function prepareParams(array $params): array
	{
		// Map enrichment data.
		$params = $this->enrichment->mapEnrichmentFields($params);

		// Remove unecesery params.
		$params = Helper::removeUneceseryParamFields($params);

		$filterName = Filters::getFilterName(['integrations', SettingsGoodbits::SETTINGS_TYPE_KEY, 'prePostParams']);
		if (\has_filter($filterName)) {
			$params = \apply_filters($filterName, $params) ?? [];
		}

		return Helper::prepareGenericParamsOutput($params);
	}

	/**
	 * Return Api Key from settings or global variable.
	 *
	 * @return string
	 */
	private function getApiKey(): string
	{
		return $this->getSettingsDisabledOutputWithDebugFilter(Variables::getApiKeyGoodbits(), SettingsGoodbits::SETTINGS_GOODBITS_API_KEY_KEY)['value'];
	}
}
