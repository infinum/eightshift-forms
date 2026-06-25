<?php

/**
 * PardotClient integration class.
 *
 * @package EightshiftForms\Integrations\Pardot
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Pardot;

use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Oauth\OauthInterface;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\DeveloperHelpers;
use EightshiftForms\Helpers\HooksHelpers;
use EightshiftForms\Troubleshooting\SettingsFallback;
use WP_Error;

/**
 * PardotClient integration class.
 */
class PardotClient implements PardotClientInterface
{
	/**
	 * Transient cache name for form handlers.
	 */
	public const CACHE_PARDOT_FORM_HANDLERS_TRANSIENT_NAME = 'es_pardot_form_handlers_cache';

	/**
	 * Transient cache name for form handler fields.
	 */
	public const CACHE_PARDOT_FORM_HANDLER_FIELDS_TRANSIENT_NAME = 'es_pardot_form_handler_fields_cache';

	/**
	 * Pardot API version.
	 */
	private const string API_VERSION = 'v5';

	/**
	 * Create a new instance that injects classes
	 *
	 * @param OauthInterface $oauthPardot Inject Oauth methods.
	 */
	public function __construct(protected OauthInterface $oauthPardot)
	{
	}

	/**
	 * Return all Pardot form handlers.
	 *
	 * @param bool $hideUpdateTime Determine if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getItems(bool $hideUpdateTime = true): array
	{
		$output = \get_transient(self::CACHE_PARDOT_FORM_HANDLERS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		if (DeveloperHelpers::isDeveloperSkipCacheActive()) {
			$output = [];
		}

		if (!$output) {
			$items = $this->getPardotFormHandlers();

			if ($items !== []) {
				foreach ($items as $item) {
					$id = (string) ($item['id'] ?? '');
					if ($id === '') {
						continue;
					}
					if ($id === '0') {
						continue;
					}

					$embedCode = $item['embedCode'] ?? '';

					$output[$id] = [
						'id' => $id,
						'title' => $item['name'] ?? '',
						'submitUrl' => $this->parseSubmitUrl($embedCode),
					];
				}

				$output[ClientInterface::TRANSIENT_STORED_TIME] = [
					'id' => ClientInterface::TRANSIENT_STORED_TIME,
					'title' => \current_datetime()->format('Y-m-d H:i:s'),
				];

				\set_transient(self::CACHE_PARDOT_FORM_HANDLERS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
			}
		}

		if ($hideUpdateTime) {
			unset($output[ClientInterface::TRANSIENT_STORED_TIME]);
		}

		return $output;
	}

	/**
	 * Return form handler fields for a given handler ID.
	 *
	 * @param string $itemId Handler ID.
	 *
	 * @return array<string, mixed>
	 */
	public function getItem(string $itemId): array
	{
		$cacheKey = self::CACHE_PARDOT_FORM_HANDLER_FIELDS_TRANSIENT_NAME;
		$output = \get_transient($cacheKey) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		if (DeveloperHelpers::isDeveloperSkipCacheActive()) {
			$output = [];
		}

		if (!$output || empty($output[$itemId])) {
			$fields = $this->getPardotFormHandlerFields($itemId);

			if ($fields !== []) {
				foreach ($fields as $field) {
					$fieldId = (string) ($field['id'] ?? '');
					$prospectApiFieldId = $field['prospectApiFieldId'] ?? '';
					if ($fieldId === '') {
						continue;
					}
					if ($fieldId === '0') {
						continue;
					}

					$output[$itemId][$fieldId] = [
						'id' => $field['name'] ?? $fieldId,
						'title' => $field['name'] ?? '',
						'dataFormat' => $field['dataFormat'] ?? 'text',
						'isRequired' => (bool) ($field['isRequired'] ?? false),
					];
				}

				\set_transient($cacheKey, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
			}
		}

		return $output[$itemId] ?? [];
	}

	/**
	 * API request to post application to Pardot form handler.
	 *
	 * @param array<string, mixed> $formDetails Form details.
	 *
	 * @return array<string, mixed>
	 */
	public function postApplication(array $formDetails): array
	{
		$params = $formDetails[Config::FD_PARAMS];
		$files = $formDetails[Config::FD_FILES];
		$formId = $formDetails[Config::FD_FORM_ID];

		$itemId = SettingsHelpers::getSettingValue(SettingsPardot::SETTINGS_PARDOT_ITEM_ID_KEY, $formId);
		$mapParams = SettingsHelpers::getSettingValueGroup(SettingsPardot::SETTINGS_PARDOT_PARAMS_MAP_KEY, $formId);

		// Filter override post request.
		$filterName = HooksHelpers::getFilterName(['integrations', SettingsPardot::SETTINGS_TYPE_KEY, 'overridePostRequest']);
		if (\has_filter($filterName)) {
			$filterValue = \apply_filters($filterName, [], $itemId, $params, $files, $formId) ?? [];

			if ($filterValue) {
				return $filterValue;
			}
		}

		$handler = $this->getItems()[$itemId] ?? [];
		$url = $handler['submitUrl'] ?? '';

		if (!$url) {
			$details = ApiHelpers::getIntegrationApiResponseDetails(
				SettingsPardot::SETTINGS_TYPE_KEY,
				new WP_Error('missing_url', 'Submit URL not found for this form handler.'),
				'',
				[],
				[],
				$itemId,
				$formId
			);
			$details[Config::IARD_MSG] = SettingsFallback::SETTINGS_FALLBACK_FLAG_PARDOT_MISSING_CONFIG;

			return ApiHelpers::getIntegrationErrorInternalOutput($details);
		}

		$body = $this->prepareParams($params, $mapParams);

		$response = \wp_remote_post(
			$url,
			[
				'headers' => [
					'Content-Type' => 'application/x-www-form-urlencoded',
				],
				'body' => $body,
				'redirection' => 0,
			]
		);

		$details = ApiHelpers::getIntegrationApiResponseDetails(
			SettingsPardot::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$params,
			$files,
			$itemId,
			$formId
		);

		$code = $details[Config::IARD_CODE];

		// Form handlers respond with 302 on success.
		if ($code >= 200 && $code < 400) {
			return ApiHelpers::getIntegrationSuccessInternalOutput($details);
		}

		$details[Config::IARD_MSG] = $this->getErrorMsg($details[Config::IARD_BODY]);

		return ApiHelpers::getIntegrationErrorInternalOutput($details);
	}

	/**
	 * Test API connection.
	 *
	 * @return array<mixed>
	 */
	public function getTestApi(): array
	{
		$url = $this->getBaseUrl('form-handlers') . 'fields=id,name';

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		$details = ApiHelpers::getIntegrationApiResponseDetails(
			SettingsPardot::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$body = $details[Config::IARD_BODY];

		if ($this->oauthPardot->hasTokenExpired($body)) {
			$refreshToken = $this->oauthPardot->getRefreshToken();

			if ($refreshToken) {
				return $this->getTestApi();
			}
		}

		return $details;
	}

	/**
	 * Map service error to fallback flag.
	 *
	 * @param array<mixed> $body Response body.
	 */
	private function getErrorMsg(array $body): string
	{
		$errorCode = $body['errorCode'] ?? '';

		return match ($errorCode) {
									'INVALID_SESSION_ID' => SettingsFallback::SETTINGS_FALLBACK_FLAG_PARDOT_ERROR_SETTINGS_MISSING,
									'SERVER_ERROR' => SettingsFallback::SETTINGS_FALLBACK_FLAG_PARDOT_SERVER_ERROR,
									'BAD_REQUEST' => SettingsFallback::SETTINGS_FALLBACK_FLAG_PARDOT_BAD_REQUEST_ERROR,
									default => SettingsFallback::SETTINGS_FALLBACK_FLAG_SUBMIT_INTEGRATION_ERROR_WP,
		};
	}

	/**
	 * Set headers for Pardot data-host calls.
	 *
	 * @return array<string, string>
	 */
	private function getHeaders(): array
	{
		$accessToken = SettingsHelpers::getOptionValue(OauthPardot::OAUTH_PARDOT_ACCESS_TOKEN_KEY);
		$businessUnitId = SettingsHelpers::getOptionWithConstant(Variables::getBusinessUnitIdPardot(), SettingsPardot::SETTINGS_PARDOT_BUSINESS_UNIT_ID);

		return [
			'Content-Type' => 'application/json',
			'Authorization' => "Bearer {$accessToken}",
			'Pardot-Business-Unit-Id' => $businessUnitId,
		];
	}

	/**
	 * Build Pardot API base URL for an object endpoint.
	 *
	 * @param string $object Object name (e.g. 'form-handlers').
	 */
	private function getBaseUrl(string $object): string
	{
		return $this->oauthPardot->getApiUrl('api/' . self::API_VERSION . '/objects/' . $object . '?');
	}

	/**
	 * Fetch all form handlers from Pardot API.
	 *
	 * @return array<mixed>
	 */
	private function getPardotFormHandlers(): array
	{
		$url = $this->getBaseUrl('form-handlers') . 'fields=id,name,embedCode,isDeleted,createdBy.username,updatedBy.username,createdAt,updatedAt';

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		$details = ApiHelpers::getIntegrationApiResponseDetails(
			SettingsPardot::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details[Config::IARD_CODE];
		$body = $details[Config::IARD_BODY];

		if ($this->oauthPardot->hasTokenExpired($body)) {
			$refreshToken = $this->oauthPardot->getRefreshToken();

			if ($refreshToken) {
				return $this->getPardotFormHandlers();
			}
		}

		if (ApiHelpers::isSuccessResponse($code)) {
			return $body['values'] ?? [];
		}

		return [];
	}

	/**
	 * Fetch fields for a specific form handler.
	 *
	 * @param string $handlerId Handler ID.
	 *
	 * @return array<mixed>
	 */
	private function getPardotFormHandlerFields(string $handlerId): array
	{
		$url = $this->getBaseUrl('form-handler-fields') . 'fields=id,name,isRequired,prospectApiFieldId,dataFormat&formHandlerId=' . \rawurlencode($handlerId);

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		$details = ApiHelpers::getIntegrationApiResponseDetails(
			SettingsPardot::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details[Config::IARD_CODE];
		$body = $details[Config::IARD_BODY];

		if ($this->oauthPardot->hasTokenExpired($body)) {
			$refreshToken = $this->oauthPardot->getRefreshToken();

			if ($refreshToken) {
				return $this->getPardotFormHandlerFields($handlerId);
			}
		}

		if (ApiHelpers::isSuccessResponse($code)) {
			return $body['values'] ?? [];
		}

		return [];
	}

	/**
	 * Parse the form handler POST URL from its embed code.
	 *
	 * @param string $embedCode Embed code HTML string.
	 */
	private function parseSubmitUrl(string $embedCode): string
	{
		if ($embedCode === '' || $embedCode === '0') {
			return '';
		}

		\preg_match('/action=["\']([^"\']+)["\']/', $embedCode, $matches);

		return $matches[1] ?? '';
	}

	/**
	 * Prepare params for form-encoded POST to form handler URL.
	 *
	 * @param array<string, mixed> $params Form params.
	 * @param array<string, string> $mapParams Mapping of form field name => Pardot field name.
	 */
	private function prepareParams(array $params, array $mapParams): string
	{
		$params = GeneralHelpers::removeUnnecessaryParamFields($params);

		$formFieldsByName = [];
		foreach ($params as $param) {
			$name = $param['name'] ?? '';
			$value = $param['value'] ?? '';

			if (!$name) {
				continue;
			}

			if (!$value) {
				continue;
			}

			if (\is_array($value)) {
				$value = \implode(',', $value);
			}

			if (\is_string($value)) {
				$value = \wp_strip_all_tags($value);
			}

			$formFieldsByName[$name] = $value;
		}

		$output = [];

		foreach ($mapParams as $formFieldName => $pardotFieldName) {
			if ($formFieldName === '') {
													continue;
			}
			if ($formFieldName === '0') {
				continue;
			}
			if (!$pardotFieldName) {
				continue;
			}
			if (isset($formFieldsByName[$formFieldName])) {
				$output[$pardotFieldName] = $formFieldsByName[$formFieldName];
			}
		}

		return \http_build_query($output);
	}
}
