<?php

/**
 * NotionbuilderClient integration class.
 *
 * @package EightshiftForms\Integrations\Notionbuilder
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Notionbuilder;

use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Oauth\OauthInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;

/**
 * NotionbuilderClient integration class.
 */
class NotionbuilderClient implements NotionbuilderClientInterface
{
	/**
	 * Transient cache name for items.
	 */
	public const CACHE_NOTIONBUILDER_ITEMS_TRANSIENT_NAME = 'es_notionbuilder_items_cache';

	/**
	 * Instance variable for Oauth.
	 *
	 * @var OauthInterface
	 */
	protected $oauthNotionbuilder;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param OauthInterface $oauthNotionbuilder Inject Oauth methods.
	 */
	public function __construct(OauthInterface $oauthNotionbuilder)
	{
		$this->oauthNotionbuilder = $oauthNotionbuilder;
	}

	/**
	 * Return projects.
	 *
	 * @param bool $hideUpdateTime Determin if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	// public function getProjects(bool $hideUpdateTime = true): array
	// {

	// 	$output = \get_transient(self::CACHE_JIRA_PROJECTS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

	// 	// Prevent cache.
	// 	if (UtilsDeveloperHelper::isDeveloperSkipCacheActive()) {
	// 		$output = [];
	// 	}

	// 	// Check if form exists in cache.
	// 	if (!$output) {
	// 		$items = $this->getJiraProjects();

	// 		if ($items) {
	// 			$fields = $this->getJiraCustomFields();

	// 			foreach ($items as $item) {
	// 				$id = $item['id'] ?? '';

	// 				$output[$id] = [
	// 					'id' => $id,
	// 					'key' => $item['key'] ?? '',
	// 					'title' => $item['name'] ?? '',
	// 					'issueTypes' => [],
	// 					'customFields' => $fields,
	// 				];
	// 			}

	// 			$output[ClientInterface::TRANSIENT_STORED_TIME] = [
	// 				'id' => ClientInterface::TRANSIENT_STORED_TIME,
	// 				'title' => \current_datetime()->format('Y-m-d H:i:s'),
	// 			];

	// 			\set_transient(self::CACHE_JIRA_PROJECTS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
	// 		}
	// 	}

	// 	if ($hideUpdateTime) {
	// 		unset($output[ClientInterface::TRANSIENT_STORED_TIME]);
	// 	}

	// 	return $output;
	// }

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
	public function postApplication(array $params, array $files, string $formId): array
	{
		// Filter override post request.
		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsNotionbuilder::SETTINGS_TYPE_KEY, 'overridePostRequest']);
		if (\has_filter($filterName)) {
			$filterValue = \apply_filters($filterName, [], $params, $files, $formId) ?? [];

			if ($filterValue) {
				return $filterValue;
			}
		}

		$url = $this->getBaseUrl('signups/push');

		$body = [
			'data' => [
				'type' => 'signups',
				'attributes' => $this->prepareParams($params, $formId),
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
			SettingsNotionbuilder::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$body,
			[],
			'',
			$formId,
			UtilsSettingsHelper::isOptionCheckboxChecked(SettingsNotionbuilder::SETTINGS_NOTIONBUILDER_SKIP_INTEGRATION_KEY, SettingsNotionbuilder::SETTINGS_NOTIONBUILDER_SKIP_INTEGRATION_KEY)
		);

		dump($details);

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
			'Accept' => 'application/json',
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
		// $url = self::BASE_URL . "fields";

		// $response = \wp_remote_get(
		// 	$url,
		// 	[
		// 		'headers' => $this->getHeaders(),
		// 	]
		// );

		// // Structure response details.
		// $details = UtilsApiHelper::getIntegrationApiReponseDetails(
		// 	SettingsMailerlite::SETTINGS_TYPE_KEY,
		// 	$response,
		// 	$url,
		// );

		// $code = $details[UtilsConfig::IARD_CODE];
		// $body = $details[UtilsConfig::IARD_BODY];

		// UtilsDeveloperHelper::setQmLogsOutput($details);

		// // On success return output.
		// if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
		// 	return $body ?? [];
		// }

		return [];
	}

	/**
	 * Get test api.
	 *
	 * @return array<mixed>
	 */
	public function getTestApi(): array
	{
		// $url = self::BASE_URL . "groups";

		// $response = \wp_remote_get(
		// 	$url,
		// 	[
		// 		'headers' => $this->getHeaders(),
		// 	]
		// );

		// // Structure response details.
		// return UtilsApiHelper::getIntegrationApiReponseDetails(
		// 	SettingsMailerlite::SETTINGS_TYPE_KEY,
		// 	$response,
		// 	$url,
		// );

		return [];
	}

	/**
	 * API request to get all lists from Mailerlite.
	 *
	 * @return array<string, mixed>
	 */
	private function getMailerliteLists()
	{
		// $details = $this->getTestApi();

		// $code = $details[UtilsConfig::IARD_CODE];
		// $body = $details[UtilsConfig::IARD_BODY];

		// UtilsDeveloperHelper::setQmLogsOutput($details);

		// // On success return output.
		// if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
		// 	return $body ?? [];
		// }

		return [];
	}

	/**
	 * Prepare params
	 *
	 * @param array<string, mixed> $params Params.
	 * @param string $formId FormId.
	 *
	 * @return array<string, mixed>
	 */
	private function prepareParams(array $params, string $formId): array
	{
		// Remove unecesery params.
		$params = UtilsGeneralHelper::removeUneceseryParamFields($params);
		$mapParams = UtilsSettingsHelper::getSettingValueGroup(SettingsNotionbuilder::SETTINGS_NOTIONBUILDER_PARAMS_MAP_KEY, $formId);
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

			$output[$mapParam] = $value;
		}

		dump($output);

		return $output;
	}

	/**
	 * Return base url.
	 *
	 * @return string
	 */
	private function getBaseUrl(string $path): string
	{
		$accessToken = UtilsSettingsHelper::getOptionValue(OauthNotionbuilder::OAUTH_NOTIONBUILDER_ACCESS_TOKEN_KEY);

		return $this->oauthNotionbuilder->getApiUrl("api/v2/{$path}?access_token={$accessToken}");
	}
}
