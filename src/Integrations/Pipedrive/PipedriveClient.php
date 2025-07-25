<?php

/**
 * Pipedrive Client integration class.
 *
 * @package EightshiftForms\Integrations\Pipedrive
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Pipedrive;

use CURLFile;
use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;

/**
 * PipedriveClient integration class.
 */
class PipedriveClient implements PipedriveClientInterface
{
	/**
	 * Return Pipedrive base url.
	 *
	 * @var string
	 */
	private const BASE_URL = 'https://api.pipedrive.com/v1/';

	/**
	 * Transient cache name for person fields.
	 */
	public const CACHE_PIPEDRIVE_PERSON_FIELDS_TRANSIENT_NAME = 'es_pipedrive_person_fields';

	/**
	 * Transient cache name for leads fields.
	 */
	public const CACHE_PIPEDRIVE_LEADS_FIELDS_TRANSIENT_NAME = 'es_pipedrive_leads_fields';

	/**
	 * Issue type epic.
	 */
	public const ISSUE_TYPE_EPIC = '10000';

	/**
	 * Return person fields.
	 *
	 * @param bool $hideUpdateTime Determin if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getPersonFields(bool $hideUpdateTime = true): array
	{

		$output = \get_transient(self::CACHE_PIPEDRIVE_PERSON_FIELDS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Prevent cache.
		if (UtilsDeveloperHelper::isDeveloperSkipCacheActive()) {
			$output = [];
		}

		// Check if form exists in cache.
		if (!$output) {
			$items = $this->getPipedrivePersonFields();

			if ($items) {
				foreach ($items as $item) {
					$isActive = $item['active_flag'] ?? false;

					if (!$isActive) {
						continue;
					}

					$id = $item['id'] ?? '';
					if (!$id) {
						continue;
					}

					$output[$id] = [
						'id' => $id,
						'key' => $item['key'] ?? '',
						'title' => $item['name'] ?? '',
						'fields' => \array_filter(\array_map(
							static function ($inner) {
								$id = $inner['id'] ?? '';
								if (!$id) {
									return [];
								}

								return [
									'id' => (string) $id,
									'title' => $inner['label'] ?? '',
								];
							},
							$item['options'] ?? []
						)),
					];
				}

				$output[ClientInterface::TRANSIENT_STORED_TIME] = [
					'id' => ClientInterface::TRANSIENT_STORED_TIME,
					'title' => \current_datetime()->format('Y-m-d H:i:s'),
				];

				\set_transient(self::CACHE_PIPEDRIVE_PERSON_FIELDS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
			}
		}

		if ($hideUpdateTime) {
			unset($output[ClientInterface::TRANSIENT_STORED_TIME]);
		}

		return $output;
	}

	/**
	 * Return leads fields.
	 *
	 * @param bool $hideUpdateTime Determin if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getLeadsFields(bool $hideUpdateTime = true): array
	{

		$output = \get_transient(self::CACHE_PIPEDRIVE_LEADS_FIELDS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Prevent cache.
		if (UtilsDeveloperHelper::isDeveloperSkipCacheActive()) {
			$output = [];
		}

		// Check if form exists in cache.
		if (!$output) {
			$items = $this->getPipedriveLeadsFields();

			if ($items) {
				foreach ($items as $item) {
					$id = $item['id'] ?? '';
					if (!$id) {
						continue;
					}

					$output[$id] = [
						'id' => (string) $id,
						'key' => $item['key'] ?? '',
						'title' => $item['name'] ?? '',
					];
				}

				$output[ClientInterface::TRANSIENT_STORED_TIME] = [
					'id' => ClientInterface::TRANSIENT_STORED_TIME,
					'title' => \current_datetime()->format('Y-m-d H:i:s'),
				];

				\set_transient(self::CACHE_PIPEDRIVE_LEADS_FIELDS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
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
	 * @param array<string, mixed> $formDetails Form details.
	 *
	 * @return array<string, mixed>
	 */
	public function postApplication(array $formDetails): array
	{
		$params = $formDetails[UtilsConfig::FD_PARAMS];
		$files = $formDetails[UtilsConfig::FD_FILES];
		$formId = $formDetails[UtilsConfig::FD_FORM_ID];

		// Filter override post request.
		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsPipedrive::SETTINGS_TYPE_KEY, 'overridePostRequest']);
		if (\has_filter($filterName)) {
			$filterValue = \apply_filters($filterName, [], $params, $files, $formId) ?? [];

			if ($filterValue) {
				return $filterValue;
			}
		}

		$organization = [];
		$lead = [];
		$person = [];
		$code = UtilsConfig::API_RESPONSE_CODE_ERROR_MISSING;
		$body = [];

		if (UtilsSettingsHelper::isSettingCheckboxChecked(SettingsPipedrive::SETTINGS_PIPEDRIVE_USE_ORGANIZATION, SettingsPipedrive::SETTINGS_PIPEDRIVE_USE_ORGANIZATION, $formId)) {
			$organization = $this->postApplicationSingle(
				'organizations',
				$this->prepareParamsOrganization($params, $formId),
				$formId
			);

			$code = $organization[UtilsConfig::IARD_CODE];
			$body = $organization[UtilsConfig::IARD_BODY];

			if ($code < UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code > UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
				$organization[UtilsConfig::IARD_MSG] = $this->getErrorMsg($body);

				return UtilsApiHelper::getIntegrationErrorInternalOutput($organization);
			}
		}

		$organizationId = $organization[UtilsConfig::IARD_BODY]['data']['id'] ?? '';

		$person = $this->postApplicationSingle(
			'persons',
			$this->prepareParams(
				$params,
				[
					'org_id' => $organizationId,
				],
				$formId
			),
			$formId
		);

		$code = $person[UtilsConfig::IARD_CODE];
		$body = $person[UtilsConfig::IARD_BODY];

		if ($code < UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code > UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			$person[UtilsConfig::IARD_MSG] = $this->getErrorMsg($body);

			return UtilsApiHelper::getIntegrationErrorInternalOutput($person);
		}

		$personId = $person[UtilsConfig::IARD_BODY]['data']['id'] ?? '';

		if (UtilsSettingsHelper::isSettingCheckboxChecked(SettingsPipedrive::SETTINGS_PIPEDRIVE_USE_LEAD, SettingsPipedrive::SETTINGS_PIPEDRIVE_USE_LEAD, $formId)) {
			$lead = $this->postApplicationSingle(
				'leads',
				$this->prepareParamsLead(
					$params,
					[
						'person_id' => $personId,
						'organization_id' => $organizationId,
					],
					$formId
				),
				$formId
			);

			$code = $lead[UtilsConfig::IARD_CODE];
			$body = $lead[UtilsConfig::IARD_BODY];

			if ($code < UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code > UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
				$lead[UtilsConfig::IARD_MSG] = $this->getErrorMsg($body);

				return UtilsApiHelper::getIntegrationErrorInternalOutput($lead);
			}
		}

		$leadId = $lead[UtilsConfig::IARD_BODY]['data']['id'] ?? '';

		if ($files) {
			$this->postFileMedia(
				$files,
				[
					'person_id' => $personId,
					'org_id' => $organizationId,
					'lead_id' => $leadId,
				]
			);
		}

		// On success return output.
		return UtilsApiHelper::getIntegrationSuccessInternalOutput($person);
	}

	/**
	 * Get test api.
	 *
	 * @return array<mixed>
	 */
	public function getTestApi(): array
	{
		$url = self::BASE_URL . "persons";

		$response = \wp_remote_get(
			$this->getApiUrl($url, 'limit=1'),
			[
				'headers' => $this->getHeaders(),
			]
		);

		return UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsPipedrive::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);
	}

	/**
	 * Upload file and attach to person.
	 *
	 * @param array<mixed> $files Files to upload from form.
	 * @param array<mixed> $additionalParams Additional body data.
	 *
	 * @return boolean
	 */
	private function postFileMedia(array $files, array $additionalParams): bool
	{
		foreach ($files as $file) {
			$fileItems = $file['value'] ?? [];

			if (!$fileItems) {
				continue;
			}

			foreach ($fileItems as $item) {
				$postData = \array_merge(
					[
					'file' => new CURLFile($item, 'multipart/form-data'),
					],
					$additionalParams
				);

				$curl = \curl_init(); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_init
				\curl_setopt_array( // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt_array
					$curl,
					[
						\CURLOPT_URL => $this->getApiUrl(self::BASE_URL . "files"),
						\CURLOPT_FAILONERROR => true,
						\CURLOPT_POST => true,
						\CURLOPT_RETURNTRANSFER => true,
						\CURLOPT_POSTFIELDS => $postData,
						\CURLOPT_HTTPHEADER => $this->getHeaders(true),
					]
				);
				\curl_close($curl); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_close
			}
		}

		return false;
	}

	/**
	 * API request to post application person.
	 *
	 * @param string $url Url to post.
	 * @param array<string, mixed> $params Params array.
	 * @param string $formId FormId value.
	 *
	 * @return array<string, mixed>
	 */
	private function postApplicationSingle(string $url, array $params, string $formId): array
	{
		$url = self::BASE_URL . $url;

		$body = $params;

		$response = \wp_remote_post(
			$this->getApiUrl($url),
			[
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode($body),
			]
		);

		return UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsPipedrive::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$body,
			[],
			'',
			$formId,
			UtilsSettingsHelper::isOptionCheckboxChecked(SettingsPipedrive::SETTINGS_PIPEDRIVE_SKIP_INTEGRATION_KEY, SettingsPipedrive::SETTINGS_PIPEDRIVE_SKIP_INTEGRATION_KEY)
		);
	}

	/**
	 * Get person fields from the api.
	 *
	 * @return array<mixed>
	 */
	private function getPipedrivePersonFields()
	{
		$url = self::BASE_URL . "personFields";

		$response = \wp_remote_get(
			$this->getApiUrl($url),
			[
				'headers' => $this->getHeaders(),
			]
		);

		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsPipedrive::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return $body['data'] ?? [];
		}

		return [];
	}

	/**
	 * Get leads from the api.
	 *
	 * @return array<mixed>
	 */
	private function getPipedriveLeadsFields()
	{
		$url = self::BASE_URL . "leadLabels";

		$response = \wp_remote_get(
			$this->getApiUrl($url),
			[
				'headers' => $this->getHeaders(),
			]
		);

		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsPipedrive::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return $body['data'] ?? [];
		}

		return [];
	}

	/**
	 * Prepare params person
	 *
	 * @param array<string, mixed> $params Params.
	 * @param array<string, mixed> $additionalParams Params additional.
	 * @param string $formId FormId value.
	 *
	 * @return array<string, string>
	 */
	private function prepareParams(array $params, array $additionalParams, string $formId): array
	{
		$output = [];

		$personName = UtilsSettingsHelper::getSettingValue(SettingsPipedrive::SETTINGS_PIPEDRIVE_PERSON_NAME_KEY, $formId);
		if (!$personName) {
			return $output;
		}

		$output['name'] = FormsHelper::getParamValue($personName, $params);

		// Remove unecesery params.
		$params = UtilsGeneralHelper::removeUneceseryParamFields($params);

		$mapParams = UtilsSettingsHelper::getSettingValueGroup(SettingsPipedrive::SETTINGS_PIPEDRIVE_PARAMS_MAP_KEY, $formId);

		foreach ($params as $param) {
			$name = $param['name'] ?? '';
			if (!$name) {
				continue;
			}

			$map = $mapParams[$name] ?? '';
			if (!$map) {
				continue;
			}

			$value = $param['value'] ?? '';
			if (!$value) {
				continue;
			}

			$output[$map] = $value;
		}

		$output['add_time'] = \gmdate("Y-m-d H:i:s");

		$label = UtilsSettingsHelper::getSettingValue(SettingsPipedrive::SETTINGS_PIPEDRIVE_LABEL_PERSON_KEY, $formId);
		if ($label) {
			$output['label'] = $label;
		}

		$output = \array_merge(
			$output,
			$additionalParams,
		);

		return $output;
	}

	/**
	 * Prepare params lead
	 *
	 * @param array<string, mixed> $params Params.
	 * @param array<string, mixed> $additionalParams Params additional.
	 * @param string $formId FormId value.
	 *
	 * @return array<string, string>
	 */
	private function prepareParamsLead(array $params, array $additionalParams, string $formId): array
	{
		$output = [];

		$leadTitle = UtilsSettingsHelper::getSettingValue(SettingsPipedrive::SETTINGS_PIPEDRIVE_LEAD_TITLE_KEY, $formId);
		if (!$leadTitle) {
			return $output;
		}

		$output['title'] = $leadTitle;


		$label = UtilsSettingsHelper::getSettingValue(SettingsPipedrive::SETTINGS_PIPEDRIVE_LABEL_LEAD_KEY, $formId);
		if ($label) {
			$output['label_ids'] = [$label];
		}

		$leadValue = UtilsSettingsHelper::getSettingValue(SettingsPipedrive::SETTINGS_PIPEDRIVE_LEAD_VALUE_KEY, $formId);
		if ($leadValue) {
			$value = FormsHelper::getParamValue($leadValue, $params);

			if (!$value) {
				$value = 0;
			}

			$output['value'] = [
				'amount' => \intval($value, 10),
				'currency' => UtilsSettingsHelper::getSettingValue(SettingsPipedrive::SETTINGS_PIPEDRIVE_LEAD_CURRENCY_KEY, $formId),
			];
		}

		$output = \array_merge(
			$output,
			$additionalParams,
		);

		return $output;
	}

	/**
	 * Prepare params organization.
	 *
	 * @param array<string, mixed> $params Params.
	 * @param string $formId FormId value.
	 *
	 * @return array<string, string>
	 */
	private function prepareParamsOrganization(array $params, string $formId): array
	{
		$output = [];

		$organization = UtilsSettingsHelper::getSettingValue(SettingsPipedrive::SETTINGS_PIPEDRIVE_ORGANIZATION_KEY, $formId);
		if (!$organization) {
			return $output;
		}

		$output['name'] = FormsHelper::getParamValue($organization, $params);

		$output['add_time'] = \gmdate("Y-m-d H:i:s");

		return $output;
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

		switch ($msg) {
			case 'Name must be given.':
				return 'pipedriveMissingName';
			case 'Organization name must be given.':
				return 'pipedriveMissingOrganization';
			case 'Invalid organization ID provided':
				return 'pipedriveWrongOrganizationId';
			case 'provided dataset is not valid':
				return 'pipedriveWrongDataset';
			default:
				return 'submitWpError';
		}
	}

	/**
	 * Get api token.
	 *
	 * @param string $url Url to get token from.
	 * @param string $params Additional params.
	 *
	 * @return string
	 */
	private function getApiUrl(string $url, string $params = ''): string
	{
		$url = \rtrim($url, '/');

		$output = $url . '/?api_token=' . $this->getApiKey();

		if ($params) {
			$output = $output . '&' . $params;
		}

		return $output;
	}

	/**
	 * Set headers used for fetching data.
	 *
	 * @param boolean $isCurl If using post method we need to send Authorization header and type in the request.
	 *
	 * @return array<mixed>
	 */
	private function getHeaders(bool $isCurl = false): array
	{
		if ($isCurl) {
			return [
				'Content-Type: multipart/form-data',
			];
		}


		return [
			'Content-Type' => 'application/json; charset=utf-8',
		];
	}

	/**
	 * Return Api Key from settings or global variable.
	 *
	 * @return string
	 */
	private function getApiKey(): string
	{
		return UtilsSettingsHelper::getOptionWithConstant(Variables::getApiKeyPipedrive(), SettingsPipedrive::SETTINGS_PIPEDRIVE_API_KEY_KEY);
	}
}
