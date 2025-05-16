<?php

/**
 * NationbuilderClient integration class.
 *
 * @package EightshiftForms\Integrations\Nationbuilder
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Nationbuilder;

use EightshiftForms\Cache\SettingsCache;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Oauth\OauthInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;

/**
 * NationbuilderClient integration class.
 */
class NationbuilderClient implements NationbuilderClientInterface
{
	/**
	 * Transient cache name for custom fields.
	 */
	public const CACHE_NATIONBUILDER_CUSTOM_FIELDS_TRANSIENT_NAME = 'es_nationbuilder_custom_fields_cache';

	/**
	 * Transient cache name for lists.
	 */
	public const CACHE_NATIONBUILDER_LISTS_TRANSIENT_NAME = 'es_nationbuilder_lists_cache';

	/**
	 * Transient cache name for tags.
	 */
	public const CACHE_NATIONBUILDER_TAGS_TRANSIENT_NAME = 'es_nationbuilder_tags_cache';

	/**
	 * Pagination page size.
	 */
	public const NATIONBUILDER_PAGINATION_PAGE_SIZE = 100;

	/**
	 * Instance variable for Oauth.
	 *
	 * @var OauthInterface
	 */
	protected $oauthNationbuilder;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param OauthInterface $oauthNationbuilder Inject Oauth methods.
	 */
	public function __construct(OauthInterface $oauthNationbuilder)
	{
		$this->oauthNationbuilder = $oauthNationbuilder;
	}

	/**
	 * Return custom fields.
	 *
	 * @param bool $hideUpdateTime Determin if update time will be in the output or not.
	 *
	 * @return array<mixed>
	 */
	public function getCustomFields(bool $hideUpdateTime = true): array
	{

		$output = \get_transient(self::CACHE_NATIONBUILDER_CUSTOM_FIELDS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Prevent cache.
		if (UtilsDeveloperHelper::isDeveloperSkipCacheActive()) {
			$output = [];
		}

		// Check if form exists in cache.
		if (!$output) {
			$items = $this->getNationbuilderCustomApiData('custom_fields', true);

			if ($items) {
				foreach ($items as $item) {
					$id = $item['attributes']['slug'] ?? '';

					$output[$id] = [
						'id' => $id,
						'title' => $item['attributes']['name'] ?? '',
					];
				}

				$output[ClientInterface::TRANSIENT_STORED_TIME] = [
					'id' => ClientInterface::TRANSIENT_STORED_TIME,
					'title' => \current_datetime()->format('Y-m-d H:i:s'),
				];

				\set_transient(self::CACHE_NATIONBUILDER_CUSTOM_FIELDS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
			}
		}

		if ($hideUpdateTime) {
			unset($output[ClientInterface::TRANSIENT_STORED_TIME]);
		}

		return $output;
	}

	/**
	 * Return lists.
	 *
	 * @param bool $hideUpdateTime Determine if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getLists(bool $hideUpdateTime = true): array
	{
		$output = \get_transient(self::CACHE_NATIONBUILDER_LISTS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Prevent cache.
		if (UtilsDeveloperHelper::isDeveloperSkipCacheActive()) {
			$output = [];
		}

		// Check if form exists in cache.
		if (!$output) {
			$items = $this->getNationbuilderCustomApiData('lists', true);

			if ($items) {
				foreach ($items as $item) {
					$id = $item['id'] ?? '';

					$output[$id] = [
						'id' => $id,
						'title' => $item['attributes']['name'] ?? '',
					];
				}

				$output[ClientInterface::TRANSIENT_STORED_TIME] = [
					'id' => ClientInterface::TRANSIENT_STORED_TIME,
					'title' => \current_datetime()->format('Y-m-d H:i:s'),
				];

				\set_transient(self::CACHE_NATIONBUILDER_LISTS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
			}
		}

		if ($hideUpdateTime) {
			unset($output[ClientInterface::TRANSIENT_STORED_TIME]);
		}

		return $output;
	}

	/**
	 * Return tags.
	 *
	 * @param bool $hideUpdateTime Determine if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getTags(bool $hideUpdateTime = true): array
	{
		$output = \get_transient(self::CACHE_NATIONBUILDER_TAGS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Prevent cache.
		if (UtilsDeveloperHelper::isDeveloperSkipCacheActive()) {
			$output = [];
		}

		// Check if form exists in cache.
		if (!$output) {
			$items = $this->getNationbuilderCustomApiData('signup_tags', true);

			if ($items) {
				foreach ($items as $item) {
					$id = $item['id'] ?? '';

					$output[$id] = [
						'id' => $id,
						'title' => $item['attributes']['name'] ?? '',
					];
				}

				$output[ClientInterface::TRANSIENT_STORED_TIME] = [
					'id' => ClientInterface::TRANSIENT_STORED_TIME,
					'title' => \current_datetime()->format('Y-m-d H:i:s'),
				];

				\set_transient(self::CACHE_NATIONBUILDER_TAGS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
			}
		}

		if ($hideUpdateTime) {
			unset($output[ClientInterface::TRANSIENT_STORED_TIME]);
		}

		return $output;
	}

	/**
	 * API request to post application.
	 *
	 * @param array<string, mixed> $params Params array.
	 * @param array<string, array<int, array<string, mixed>>> $files Files array.
	 * @param string $formId FormId value.
	 *
	 * @return array<string, mixed>
	 */
	public function postApplication(array $params, array $files, string $formId): array
	{
		// Filter override post request.
		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsNationbuilder::SETTINGS_TYPE_KEY, 'overridePostRequest']);
		if (\has_filter($filterName)) {
			$filterValue = \apply_filters($filterName, [], $params, $files, $formId) ?? [];

			if ($filterValue) {
				return $filterValue;
			}
		}

		$url = $this->getBaseUrl('signups');

		$body = [
			'data' => [
				'type' => 'signups',
				'attributes' => $this->prepareParams($params, $formId),
			]
		];

		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode($body),
			]
		);

		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsNationbuilder::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$body,
			[],
			'',
			$formId,
			UtilsSettingsHelper::isOptionCheckboxChecked(SettingsNationbuilder::SETTINGS_NATIONBUILDER_SKIP_INTEGRATION_KEY, SettingsNationbuilder::SETTINGS_NATIONBUILDER_SKIP_INTEGRATION_KEY)
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		if ($this->oauthNationbuilder->hasTokenExpired($body)) {
			$refreshToken = $this->oauthNationbuilder->getRefreshToken();

			if ($refreshToken) {
				return $this->postApplication($params, $files, $formId);
			}
		}

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			$list = UtilsSettingsHelper::getSettingValue(SettingsNationbuilder::SETTINGS_NATIONBUILDER_LIST_KEY, $formId);
			$tags = \explode(UtilsConfig::DELIMITER, UtilsSettingsHelper::getSettingValue(SettingsNationbuilder::SETTINGS_NATIONBUILDER_TAGS_KEY, $formId));

			if ($list || $tags) {
				$job = UtilsSettingsHelper::getOptionValueGroup(SettingsNationbuilder::SETTINGS_NATIONBUILDER_CRON_KEY);

				if ($list) {
					$job['list'][$list][] = $body['data']['id'] ?? '';
				}

				if ($tags) {
					foreach ($tags as $tag) {
						$job['tags'][$tag][] = $body['data']['id'] ?? '';
					}
				}

				\update_option(UtilsSettingsHelper::getSettingName(SettingsNationbuilder::SETTINGS_NATIONBUILDER_CRON_KEY), $job);
			}

			return UtilsApiHelper::getIntegrationSuccessInternalOutput($details);
		}

		$details[UtilsConfig::IARD_VALIDATION] = $this->getFieldsErrors($body, $formId);
		$details[UtilsConfig::IARD_MSG] = $this->getErrorMsg($body);

		// Output error.
		return UtilsApiHelper::getIntegrationErrorInternalOutput($details);
	}

	/**
	 * API request to post list.
	 *
	 * @param string $listId List id.
	 * @param string $signupId Signup id.
	 *
	 * @return array<string, mixed>
	 */
	public function postList(string $listId, string $signupId): array
	{
		$url = $this->getBaseUrl("lists/{$listId}/add_signups");

		$body = [
			'data' => [
				'id' => $listId,
				'type' => 'lists',
				'signup_ids' => [
					$signupId,
				],
			]
		];

		$response = \wp_remote_post(
			$url,
			[
				'method' => 'PATCH',
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode($body),
			]
		);

		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsNationbuilder::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$body,
			[],
			'',
			'',
			UtilsSettingsHelper::isOptionCheckboxChecked(SettingsNationbuilder::SETTINGS_NATIONBUILDER_SKIP_INTEGRATION_KEY, SettingsNationbuilder::SETTINGS_NATIONBUILDER_SKIP_INTEGRATION_KEY)
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		if ($this->oauthNationbuilder->hasTokenExpired($body)) {
			$refreshToken = $this->oauthNationbuilder->getRefreshToken();

			if ($refreshToken) {
				return $this->postList($listId, $signupId);
			}
		}

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return UtilsApiHelper::getIntegrationSuccessInternalOutput($details);
		}

		// Output error.
		return UtilsApiHelper::getIntegrationErrorInternalOutput($details);
	}

	/**
	 * API request to post tag.
	 *
	 * @param string $tagId Tag id.
	 * @param string $signupId Signup id.
	 *
	 * @return array<string, mixed>
	 */
	public function postTag(string $tagId, string $signupId): array
	{
		$url = $this->getBaseUrl('signup_taggings');

		$body = [
			'data' => [
				'type' => 'signup_taggings',
				'attributes' => [
					'signup_id' => $signupId,
					'tag_id' => $tagId,
				],
			]
		];

		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode($body),
			]
		);

		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsNationbuilder::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$body,
			[],
			'',
			'',
			UtilsSettingsHelper::isOptionCheckboxChecked(SettingsNationbuilder::SETTINGS_NATIONBUILDER_SKIP_INTEGRATION_KEY, SettingsNationbuilder::SETTINGS_NATIONBUILDER_SKIP_INTEGRATION_KEY)
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		if ($this->oauthNationbuilder->hasTokenExpired($body)) {
			$refreshToken = $this->oauthNationbuilder->getRefreshToken();

			if ($refreshToken) {
				return $this->postTag($tagId, $signupId);
			}
		}

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return UtilsApiHelper::getIntegrationSuccessInternalOutput($details);
		}

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
		$msg = $body['code'] ?? '';

		switch ($msg) {
			case 'bad_request':
				return 'mailerliteBadRequestError';
			case 'unauthorized':
				return 'nationbuilderErrorSettingsMissing';
			case 'server_error':
				return 'nationbuilderServerError';
			default:
				return 'submitWpError';
		}
	}

	/**
	 * Map service messages for fields with our own.
	 *
	 * @param array<mixed> $body API response body.
	 * @param string $formId FormId value.
	 *
	 * @return array<string, string>
	 */
	private function getFieldsErrors(array $body, string $formId): array
	{
		$errors = $body['errors'] ?? [];
		$output = [];

		$mapParams = \array_flip(UtilsSettingsHelper::getSettingValueGroup(SettingsNationbuilder::SETTINGS_NATIONBUILDER_PARAMS_MAP_KEY, $formId));

		foreach ($errors as $error) {
			$message = $error['detail'] ?? '';
			$key = $error['meta']['attribute'] ?? '';

			if (!$message || !$key || !isset($mapParams[$key])) {
				continue;
			}

			if (\str_contains($message, 'E-mail email is already taken on signup')) {
				$output[$mapParams[$key]] = 'validationEmailExists';
				continue;
			}
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
			'Accept' => 'application/json',
		];

		return $headers;
	}

	/**
	 * API request to get all custom data from endpoints.
	 *
	 * @param string $endpoint Endpoint.
	 * @param bool $paginate Use pagination.
	 * @param array<string, mixed> $pagedData Paged data.
	 * @param string $nextEndpoint Next page endpoint.
	 *
	 * @return array<string, mixed>
	 */
	private function getNationbuilderCustomApiData(string $endpoint, bool $paginate = false, array $pagedData = [], string $nextEndpoint = ''): array
	{
		$url = $paginate && !empty($nextEndpoint) ? $this->getNextUrl($nextEndpoint) : $this->setPaginationAttributes($this->getBaseUrl($endpoint));

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsNationbuilder::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		if ($this->oauthNationbuilder->hasTokenExpired($body)) {
			$refreshToken = $this->oauthNationbuilder->getRefreshToken();

			if ($refreshToken) {
				return $this->getNationbuilderCustomApiData($endpoint);
			}
		}

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			// Check if we need to paginate.
			if ($paginate) {
				$pagedData = \array_merge($pagedData, $body['data'] ?? []);
				$next = $body['links']['next'] ?? '';

				if (!empty($next)) {
					return $this->getNationbuilderCustomApiData($endpoint, true, $pagedData, $next);
				}

				return $pagedData;
			}

			return $body['data'] ?? [];
		}

		// In case of pagination error, return the data we have.
		return $pagedData ?: [];
	}

	/**
	 * Prepare params.
	 *
	 * @param array<string, mixed> $params Params.
	 * @param string $formId FormId.
	 *
	 * @return array<string, mixed>
	 */
	private function prepareParams(array $params, string $formId): array
	{
		// Remove unnecessary params.
		$params = UtilsGeneralHelper::removeUneceseryParamFields($params);
		$mapParams = UtilsSettingsHelper::getSettingValueGroup(SettingsNationbuilder::SETTINGS_NATIONBUILDER_PARAMS_MAP_KEY, $formId);
		$output = [];

		if (!$mapParams || !$params) {
			return $output;
		}

		foreach ($mapParams as $mapParamKey => $mapParam) {
			$param = UtilsGeneralHelper::getFieldDetailsByName($params, $mapParamKey);

			$value = $param['value'] ?? '';
			$name = $param['name'] ?? '';
			$type = $param['type'] ?? '';

			if (!$value || !$name || !$type || !$param || !$mapParam) {
				continue;
			}

			if ($type === 'file') {
				$value = \array_map(
					static function (string $file) {
						$filename = \pathinfo($file, \PATHINFO_FILENAME);
						$extension = \pathinfo($file, \PATHINFO_EXTENSION);
						return "{$filename}.{$extension}";
					},
					$value
				);
			}

			if (\is_array($value)) {
				$value = \implode(', ', $value);
			}

			if (isset($this->getCustomFields()[$mapParam])) {
				$output['custom_values'][$mapParam] = $value;
			} else {
				$output[$mapParam] = $value;
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
		$url = $this->getBaseUrl('lists');

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsNationbuilder::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$body = $details[UtilsConfig::IARD_BODY];

		if ($this->oauthNationbuilder->hasTokenExpired($body)) {
			$refreshToken = $this->oauthNationbuilder->getRefreshToken();

			if ($refreshToken) {
				return $this->getTestApi();
			}
		}

		// Structure response details.
		return UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsNationbuilder::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);
	}

	/**
	 * Return base url.
	 *
	 * @param string $path Path.
	 *
	 * @return string
	 */
	private function getBaseUrl(string $path): string
	{
		$accessToken = UtilsSettingsHelper::getOptionValue(OauthNationbuilder::OAUTH_NATIONBUILDER_ACCESS_TOKEN_KEY);

		return $this->oauthNationbuilder->getApiUrl("api/v2/{$path}?access_token={$accessToken}");
	}

	/**
	 * Return pagination next url.
	 *
	 * @param string $path Path.
	 *
	 * @return string
	 */
	private function getNextUrl(string $path): string
	{
		return $this->oauthNationbuilder->getApiUrl(\ltrim($path, '/'));
	}

	/**
	 * Return a path with pagination attributes url.
	 *
	 * @param string $path Path.
	 *
	 * @return string
	 */
	private function setPaginationAttributes(string $path): string
	{
		return \add_query_arg(
			[
				'page[number]' => 1,
				'page[size]' => self::NATIONBUILDER_PAGINATION_PAGE_SIZE,
			],
			$path
		);
	}
}
