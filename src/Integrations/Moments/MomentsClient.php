<?php

/**
 * Moments Client integration class.
 *
 * @package EightshiftForms\Integrations\Moments
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Moments;

use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;

/**
 * MomentsClient integration class.
 */
class MomentsClient extends AbstractMoments implements ClientInterface
{
	/**
	 * Transient cache name for items.
	 */
	public const CACHE_MOMENTS_ITEMS_TRANSIENT_NAME = 'es_moments_items_cache';

	/**
	 * Transient cache name for IBSSO Token.
	 */
	public const CACHE_MOMENTS_TOKEN_TRANSIENT_NAME = 'es_moments_token_cache';

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
		$output = \get_transient(self::CACHE_MOMENTS_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Prevent cache.
		if (UtilsDeveloperHelper::isDeveloperSkipCacheActive()) {
			$output = [];
		}

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

				\set_transient(self::CACHE_MOMENTS_ITEMS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
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
		$body = $this->prepareParams($params, $formId);

		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsMoments::SETTINGS_TYPE_KEY, 'prePostId']);
		if (\has_filter($filterName)) {
			$itemId = \apply_filters($filterName, $itemId, $body, $formId) ?? $itemId;
		}

		$url = "{$this->getBaseUrl()}forms/1/forms/{$itemId}/data";

		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode($body),
			]
		);

		// Structure response details.
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsMoments::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$body,
			$files,
			$itemId,
			$formId,
			UtilsSettingsHelper::isOptionCheckboxChecked(SettingsMoments::SETTINGS_MOMENTS_SKIP_INTEGRATION_KEY, SettingsMoments::SETTINGS_MOMENTS_SKIP_INTEGRATION_KEY)
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
	 * Map service messages with our own.
	 *
	 * @param array<mixed> $body API response body.
	 *
	 * @return string
	 */
	private function getErrorMsg(array $body): string
	{
		$msg = $body['requestError']['serviceException']['messageId'] ?? '';

		switch ($msg) {
			case 'BAD_REQUEST':
				return 'momentsBadRequestError';
			case 'UNAUTHORIZED':
				return 'momentsErrorSettingsMissing';
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
		$msg = $body['requestError']['serviceException']['text'] ?? '';
		$output = [];

		if (!$msg) {
			return [];
		}

		// Validate req fields.
		\preg_match_all("/(No data was submitted for a mandatory field: )(\w*)/", $msg, $matchesReq, \PREG_SET_ORDER, 0);

		if ($matchesReq) {
			$key = $matchesReq[0][2] ?? '';
			if ($key) {
				$output[$key] = 'validationRequired';
			}
		}

		// Validate invalid email field.
		\preg_match_all("/(\w*) (should have a valid email format)/", $msg, $matchesEmail, \PREG_SET_ORDER, 0);

		if ($matchesEmail) {
			$key = $matchesEmail[0][1] ?? '';

			if ($key) {
				$output[$key] = 'validationEmail';
			}
		}

		// Validate invalid phone field.
		\preg_match_all("/(\w*) (is not a valid phone number)/", $msg, $matchesPhone, \PREG_SET_ORDER, 0);

		if ($matchesPhone) {
			$key = $matchesPhone[0][1] ?? '';

			if ($key) {
				$output[$key] = 'validationPhone';
			}
		}

		// Validate invalid phone prefix field.
		\preg_match_all("/(\w*) (number does not have valid country\/network prefix)/", $msg, $matchesPhonePrefix, \PREG_SET_ORDER, 0);

		if ($matchesPhonePrefix) {
			$key = $matchesPhonePrefix[0][1] ?? '';

			if ($key) {
				$output[$key] = 'validationPhone';
			}
		}

		// Validate invalid phone field.
		\preg_match_all("/(\w*) (number is not numeric)/", $msg, $matchesPhoneIsNumberic, \PREG_SET_ORDER, 0);

		if ($matchesPhoneIsNumberic) {
			$key = $matchesPhoneIsNumberic[0][1] ?? '';

			if ($key) {
				$output[$key] = 'validationPhone';
			}
		}

		// Validate invalid phone length field.
		\preg_match_all("/(\w*) (number has invalid length for network)/", $msg, $matchesPhoneLength, \PREG_SET_ORDER, 0);

		if ($matchesPhoneLength) {
			$key = $matchesPhoneLength[0][1] ?? '';

			if ($key) {
				$output[$key] = 'validationMomentsInvalidPhoneLenght';
			}
		}

		// Validate invalid datetime field.
		\preg_match_all("/(\w*) (should be an ISO datetime, but there is)/", $msg, $matchesDate, \PREG_SET_ORDER, 0);

		if ($matchesDate) {
			$key = $matchesDate[0][1] ?? '';

			if ($key) {
				$output[$key] = 'validationDateTime';
			}
		}

		// Validate invalid date field.
		\preg_match_all("/(\w*) (should be an ISO date, but there is)/", $msg, $matchesDate, \PREG_SET_ORDER, 0);

		if ($matchesDate) {
			$key = $matchesDate[0][1] ?? '';

			if ($key) {
				$output[$key] = 'validationDate';
			}
		}

		// Validate invalid date field.
		\preg_match_all("/(\w*) (should be earlier than current date)/", $msg, $matchesDateNoFuture, \PREG_SET_ORDER, 0);

		if ($matchesDateNoFuture) {
			$key = $matchesDateNoFuture[0][1] ?? '';

			if ($key) {
				$output[$key] = 'validationDateNoFuture';
			}
		}

		// Validate invalid country field.
		\preg_match_all("/(\w*) (should be one of valid options)/", $msg, $matchesCountry, \PREG_SET_ORDER, 0);

		if ($matchesCountry) {
			$key = $matchesCountry[0][1] ?? '';

			if ($key) {
				$output[$key] = 'validationInvalid';
			}
		}

		// Validate invalid phone length field.
		\preg_match_all("/(\w*) (contains forbidden special characters)/", $msg, $matchesForbiddenCharacters, \PREG_SET_ORDER, 0);

		if ($matchesForbiddenCharacters) {
			$key = $matchesForbiddenCharacters[0][1] ?? '';

			if ($key) {
				$output[$key] = 'validationMomentsInvalidSpecialCharacters';
			}
		}

		return $output;
	}

	/**
	 * Get test api.
	 *
	 * @return array<mixed>
	 */
	public function getTestApi(): array
	{
		$url = "{$this->getBaseUrl()}/forms/1/forms?limit=1";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		return UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsMoments::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);
	}

	/**
	 * API request to get all lists from Moments.
	 *
	 * @return array<string, mixed>
	 */
	private function getMomentsLists()
	{
		$url = "{$this->getBaseUrl()}/forms/1/forms?limit=100";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsMoments::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		UtilsDeveloperHelper::setQmLogsOutput($details);

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return $body['forms'] ?? [];
		}

		return [];
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
		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsMoments::SETTINGS_TYPE_KEY, 'prePostParams']);
		if (\has_filter($filterName)) {
			$params = \apply_filters($filterName, $params, $formId) ?? [];
		}

		// Remove unecesery params.
		$params = UtilsGeneralHelper::removeUneceseryParamFields($params);

		foreach ($params as $param) {
			$type = $param['type'] ?? '';

			$value = $param['value'] ?? '';
			if (!$value) {
				continue;
			}

			$name = $param['name'] ?? '';
			if (!$name) {
				continue;
			}

			switch ($type) {
				case 'select':
					$explode = \explode(UtilsConfig::DELIMITER, $value);

					$value = \count($explode) > 1 ? $explode : $value;
					break;
				case 'checkbox':
					$value = \explode(UtilsConfig::DELIMITER, $value);
					break;
				case 'phone':
					$value = \filter_var($value, \FILTER_SANITIZE_NUMBER_INT);
					$value = \ltrim($value, '0');
					break;
			}

			$typeCustom = $param['typeCustom'] ?? '';
			switch ($typeCustom) {
				case 'email':
					$value = \strtolower($value);
					break;
			}

			$output[$name] = $value;
		}

		return $output;
	}
}
