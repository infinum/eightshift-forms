<?php

/**
 * HubSpot Client integration class.
 *
 * @package EightshiftForms\Integrations\Hubspot
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Hubspot;

use CURLFile;
use EightshiftForms\Cache\SettingsCache;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftForms\Security\SecurityInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

/**
 * HubspotClient integration class.
 */
class HubspotClient implements HubspotClientInterface
{
	/**
	 * Transient cache name for items.
	 */
	public const CACHE_HUBSPOT_ITEMS_TRANSIENT_NAME = 'es_hubspot_items_cache';

	/**
	 * Transient cache name for contact properties.
	 */
	public const CACHE_HUBSPOT_CONTACT_PROPERTIES_TRANSIENT_NAME = 'es_hubspot_contact_properties_cache';

	/**
	 * Filemanager default folder.
	 */
	public const HUBSPOT_FILEMANAGER_DEFAULT_FOLDER_KEY = 'esforms';

	/**
	 * Consent constants.
	 */
	public const HUBSPOT_CONSENT_COMMUNICATION = 'communication';
	public const HUBSPOT_CONSENT_PROCESSING = 'processing';
	public const HUBSPOT_CONSENT_LEGITIMATE = 'legitimateInterest';

	/**
	 * Instance variable of enrichment data.
	 *
	 * @var EnrichmentInterface
	 */
	protected EnrichmentInterface $enrichment;

	/**
	 * Instance variable of SecurityInterface data.
	 *
	 * @var SecurityInterface
	 */
	protected $security;

	/**
	 * Create a new admin instance.
	 *
	 * @param EnrichmentInterface $enrichment Inject enrichment which holds data about for storing to localStorage.
	 * @param SecurityInterface $security Inject security methods.
	 */
	public function __construct(
		EnrichmentInterface $enrichment,
		SecurityInterface $security
	) {
		$this->enrichment = $enrichment;
		$this->security = $security;
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
		$output = \get_transient(self::CACHE_HUBSPOT_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Prevent cache.
		if (UtilsDeveloperHelper::isDeveloperSkipCacheActive()) {
			$output = [];
		}

		// Check if form exists in cache.
		if (!$output) {
			$items = $this->getHubspotItems();

			if ($items) {
				foreach ($items as $item) {
					$id = $item['guid'] ?? '';

					if (!$id) {
						continue;
					}

					$fields = $item['formFieldGroups'] ?? [];

					$portalId = $item['portalId'] ?? '';
					$delimiter = UtilsConfig::DELIMITER;
					$value = "{$id}{$delimiter}{$portalId}";

					$output[$value] = [
						'id' => $value,
						'title' => $item['name'] ?? '',
						'fields' => $fields,
						'submitButtonText' => $item['submitText'] ?? '',
						'consent' => $this->getConsentData($item),
					];
				}

				$output[ClientInterface::TRANSIENT_STORED_TIME] = [
					'id' => ClientInterface::TRANSIENT_STORED_TIME,
					'title' => \current_datetime()->format('Y-m-d H:i:s'),
				];

				\set_transient(self::CACHE_HUBSPOT_ITEMS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
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
	 * @param string $itemId Item id to search by.
	 *
	 * @return array<string, mixed>
	 */
	public function getItem(string $itemId): array
	{
		return $this->getItems()[$itemId] ?? [];
	}

	/**
	 * Return contact properties with cache option for faster loading.
	 *
	 * @return array<string, mixed>
	 */
	public function getContactProperties(): array
	{
		$output = \get_transient(self::CACHE_HUBSPOT_CONTACT_PROPERTIES_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Prevent cache.
		if (UtilsDeveloperHelper::isDeveloperSkipCacheActive()) {
			$output = [];
		}

		if (empty($output)) {
			$items = $this->getHubspotContactProperties();

			$output = [];

			$allowedTypes = [
				'text' => 0,
				'textarea' => 1,
			];

			foreach ($items as $item) {
				$name = $item['name'] ?? '';
				$hidden = $item['hidden'] ?? false;
				$readOnlyValue = $item['readOnlyValue'] ?? false;
				$formField = $item['formField'] ?? false;
				$deleted = $item['deleted'] ?: false; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
				$fieldType = $item['fieldType'] ?? '';

				if (!$name || $hidden || $readOnlyValue || !$formField || $deleted) {
					continue;
				}

				if (!isset($allowedTypes[$fieldType])) {
					continue;
				}

				$output[] = $name;
			}

			\sort($output);

			\set_transient(self::CACHE_HUBSPOT_CONTACT_PROPERTIES_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
		}

		return $output;
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
		$paramsPrepared = $this->prepareParams($params, $formId);
		$paramsFiles = $this->prepareFiles($files, $formId);

		$body = [
			'context' => [
				'ipAddress' => $this->security->getIpAddress(),
				'hutk' => $params[UtilsHelper::getStateParam('hubspotCookie')]['value'] ?? '',
				'pageUri' => UtilsGeneralHelper::cleanPageUrl($params[UtilsHelper::getStateParam('hubspotPageUrl')]['value'] ?? ''),
				'pageName' => $params[UtilsHelper::getStateParam('hubspotPageName')]['value'] ?? '',
			],
		];

		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsHubspot::SETTINGS_TYPE_KEY, 'prePostId']);
		if (\has_filter($filterName)) {
			$itemId = \apply_filters($filterName, $itemId, $paramsPrepared, $formId) ?? $itemId;
		}

		$itemIdExploded = \explode(UtilsConfig::DELIMITER, $itemId);

		$baseId = $itemIdExploded[1] ?? '';
		$submitId = $itemIdExploded[0] ?? '';

		$paramsConsent = $this->prepareConsent($params, $itemId);
		if ($paramsConsent) {
			$body['legalConsentOptions'] = $paramsConsent;
		}

		$body['fields'] = \array_merge(
			$paramsPrepared,
			$paramsFiles
		);

		$url = $this->getBaseUrl("submissions/v3/integration/secure/submit/{$baseId}/{$submitId}");

		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode($body),
			]
		);

		// Structure response details.
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsHubspot::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$paramsPrepared,
			$paramsFiles,
			$itemId,
			$formId,
			UtilsSettingsHelper::isOptionCheckboxChecked(SettingsHubspot::SETTINGS_HUBSPOT_SKIP_INTEGRATION_KEY, SettingsHubspot::SETTINGS_HUBSPOT_SKIP_INTEGRATION_KEY)
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
	 * Post contact property to HubSpot.
	 *
	 * @param string $email Email to connect data to.
	 * @param array<string, mixed> $params Params array.
	 *
	 * @return array<string, mixed>
	 */
	public function postContactProperty(string $email, array $params): array
	{
		$properties = [];

		$customFields = \array_flip(Components::flattenArray(UtilsHelper::getStateParams()));

		if ($params) {
			foreach ($params as $key => $value) {
				// Remove unecesery fields.
				if (isset($customFields[$key])) {
					continue;
				}

				$properties[] = [
					'property' => $key,
					'value' => $value,
				];
			}
		}

		$body = [
			'properties' => $properties,
		];

		$url = $this->getBaseUrl("contacts/v1/contact/createOrUpdate/email/{$email}", true);

		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode($body),
			]
		);

		// Structure response details.
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsHubspot::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$body
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		UtilsDeveloperHelper::setQmLogsOutput($details);

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return UtilsApiHelper::getIntegrationSuccessInternalOutput($details);
		}

		$details[UtilsConfig::IARD_MSG] = $this->getErrorMsg($body);

		// Output error.
		return UtilsApiHelper::getIntegrationErrorInternalOutput($details);
	}

	/**
	 * Get post file media sent to HubSpot file manager.
	 *
	 * @param string $file File to send.
	 * @param string $formId FormId value.
	 *
	 * @return string
	 */
	private function postFileMedia(string $file, string $formId): string
	{
		$folder = UtilsSettingsHelper::getSettingValue(SettingsHubspot::SETTINGS_HUBSPOT_FILEMANAGER_FOLDER_KEY, $formId);

		if (!$folder) {
			$folder = self::HUBSPOT_FILEMANAGER_DEFAULT_FOLDER_KEY;
		}

		$options = [
			'folderPath' => '/' . $folder,
			'options' => \wp_json_encode([
				"access" => "PUBLIC_NOT_INDEXABLE",
				"overwrite" => false,
			]),
		];

		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsHubspot::SETTINGS_TYPE_KEY, 'filesOptions']);
		if (\has_filter($filterName)) {
			$options = \apply_filters($filterName, []);
		}

		$postData = \array_merge(
			[
				'file' => new CURLFile($file, 'application/octet-stream'),
			],
			$options
		);

		$curl = \curl_init(); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_init
		\curl_setopt_array( // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt_array
			$curl,
			[
				\CURLOPT_URL => $this->getBaseUrl("filemanager/api/v3/files/upload", true),
				\CURLOPT_FAILONERROR => true,
				\CURLOPT_POST => true,
				\CURLOPT_RETURNTRANSFER => true,
				\CURLOPT_POSTFIELDS => $postData,
				\CURLOPT_HTTPHEADER => $this->getHeaders(true),
			]
		);

		$response = \curl_exec($curl); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_exec
		$code = \curl_getinfo($curl, \CURLINFO_HTTP_CODE); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_getinfo
		\curl_close($curl); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_close

		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			$response = \json_decode((string) $response, true);

			return $response['objects'][0]['url'] ?? '';
		}

		return '';
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
		$msg = $body['message'] ?? '';
		$errors = $body['errors'] ?? [];

		if ($errors && isset($errors[0])) {
			$msg = $errors[0]['errorType'];
		}

		switch ($msg) {
			// Internal.
			case 'Bad Request':
				return 'hubspotBadRequestError';
			case 'The request is not valid':
				return 'hubspotInvalidRequestError';

			// Hubspot.
			case 'MAX_NUMBER_OF_SUBMITTED_VALUES_EXCEEDED':
				return 'hubspotMaxNumberOfSubmittedValuesExceededError';
			case 'INVALID_EMAIL':
				return 'hubspotInvalidEmailError';
			case 'BLOCKED_EMAIL':
				return 'hubspotBlockedEmailError';
			case 'INVALID_NUMBER':
				return 'hubspotInvalidNumberError';
			case 'INPUT_TOO_LARGE':
				return 'hubspotInputTooLargeError';
			case 'FIELD_NOT_IN_FORM_DEFINITION':
				return 'hubspotFieldNotInFormDefinitionError';
			case 'NUMBER_OUT_OF_RANGE':
				return 'hubspotNumberOutOfRangeError';
			case 'VALUE_NOT_IN_FIELD_DEFINITION':
				return 'hubspotValueNotInFieldDefinitionError';
			case 'INVALID_METADATA':
				return 'hubspotInvalidMetadataError';
			case 'INVALID_GOTOWEBINAR_WEBINAR_KEY':
				return 'hubspotInvalidGotowebinarWebinarKeyError';
			case 'INVALID_HUTK':
				return 'hubspotInvalidHutkError';
			case 'INVALID_IP_ADDRESS':
				return 'hubspotInvalidIpAddressError';
			case 'INVALID_PAGE_URI':
				return 'hubspotInvalidPageUriError';
			case 'INVALID_LEGAL_OPTION_FORMAT':
				return 'hubspotInvalidLegalOptionFormatError';
			case 'MISSING_PROCESSING_CONSENT':
				return 'hubspotMissingProcessingConsentError';
			case 'MISSING_PROCESSING_CONSENT_TEXT':
				return 'hubspotMissingProcessingConsentTextError';
			case 'MISSING_COMMUNICATION_CONSENT_TEXT':
				return 'hubspotMissingCommunicationConsentTextError';
			case 'MISSING_LEGITIMATE_INTEREST_TEXT':
				return 'hubspotMissingLegitimateInterestTextError';
			case 'DUPLICATE_SUBSCRIPTION_TYPE_ID':
				return 'hubspotDuplicateSubscriptionTypeIdError';
			case 'FORM_HAS_RECAPTCHA_ENABLED':
				return 'hubspotHasRecaptchaEnabledError';
			case 'ERROR 429	':
				return 'hubspotError429Error';
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
		$msg = $body['errors'] ?? [];
		$output = [];

		foreach ($msg as $value) {
			$key = $value['errorType'] ?? '';
			$message = $value['message'] ?? '';

			if (!$key || !$message) {
				continue;
			}

			if ($key === 'REQUIRED_FIELD') {
				// Validate req fields.
				\preg_match_all("/(Required field) '(\w+)' (is missing)/", $message, $matchesReq, \PREG_SET_ORDER, 0);

				if ($matchesReq) {
					$match = $matchesReq[0][2] ?? '';
					if ($match) {
						$output[$match] = 'validationRequired';
					}
				}
			}
		}

		return $output;
	}

	/**
	 * API request to get contact properties from Hubspot.
	 *
	 * @return array<string, mixed>
	 */
	private function getHubspotContactProperties()
	{
		$url = $this->getBaseUrl('properties/v1/contacts/properties', true);

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsHubspot::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		UtilsDeveloperHelper::setQmLogsOutput($details);

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
		$url = $this->getBaseUrl('forms/v2/forms', true);

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		return UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsHubspot::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);
	}

	/**
	 * API request to get all items from Hubspot.
	 *
	 * @return array<string, mixed>
	 */
	private function getHubspotItems()
	{
		$details = $this->getTestApi();

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		UtilsDeveloperHelper::setQmLogsOutput($details);

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			return $body ?? [];
		}

		return [];
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
				'Authorization: Bearer ' . $this->getApiKey(),
			];
		}

		return [
			'Content-Type' => 'application/json; charset=utf-8',
			'Authorization' => "Bearer {$this->getApiKey()}"
		];
	}

	/**
	 * Populate and prepare consent checkboxes.
	 *
	 * @param array<string, mixed> $item Form data got from the api.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function getConsentData(array $item): array
	{
		// Find consent data from meta.
		$consentData = \array_values(\array_filter(\array_map(
			static function ($item) {
				$name = $item['name'] ?? '';

				if ($name === 'legalConsentOptions') {
					return $item['value'] ?? '';
				}
			},
			$item['metaData'] ?? [],
		)));

		$consentData = $consentData[0] ?? '';

		// Validate if json.
		if (!Components::isJson($consentData)) {
			return [];
		}

		// Check for consent data.
		if (!$consentData) {
			return [];
		}

		$consentData = \json_decode($consentData, true);
		if (!$consentData) {
			return [];
		}

		$output = [];

		$type = $consentData['processingConsentType'] ?? '';
		$interest = $consentData['isLegitimateInterest'] ?? false;

		$output[self::HUBSPOT_CONSENT_COMMUNICATION] = [
			'items' => \array_map(
				static function ($item) {
					return [
						'id' => isset($item['communicationTypeId']) ? \strval($item['communicationTypeId']) : '',
						'label' => $item['label'] ?? '',
						'isRequired' => $item['required'] ?? false,
					];
				},
				$consentData['communicationConsentCheckboxes'] ?? []
			),
			'text' => $consentData['communicationConsentText'] ?? '',
			'isHidden' => $type === 'IMPLICIT' && $interest,
		];

		$output[self::HUBSPOT_CONSENT_PROCESSING] = [
			'type' => $type,
			'text' => $consentData['processingConsentText'] ?? '',
			'label' => $consentData['processingConsentCheckboxLabel'] ?? '',
			'isHidden' => ($type === 'IMPLICIT' && $interest) || ($type === 'IMPLICIT' && !$interest),
		];

		$output[self::HUBSPOT_CONSENT_LEGITIMATE] = [
			'typeId' => $consentData['legitimateInterestSubscriptionTypes'][0] ?? '',
			'basis' => 'CUSTOMER',
			'isActive' => !!$interest,
			'text' => $consentData['privacyPolicyText'] ?? '',
			'isHidden' => true,
		];

		return $output;
	}

	/**
	 * Prepare params
	 *
	 * @param array<string, mixed> $params Params.
	 * @param string $itemId ItemID.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function prepareConsent(array $params, string $itemId): array
	{
		$output = [];

		$data = $this->getItem($itemId)['consent'] ?? [];

		if (!$data) {
			return $output;
		}

		$communicationOutput = [];

		foreach ($params as $param) {
			$typeCustom = $param['typeCustom'] ?? '';
			$value = $param['value'] ?? '';
			$name = $param['name'] ? \explode(UtilsConfig::DELIMITER, $param['name']) : [];

			if ($data[self::HUBSPOT_CONSENT_LEGITIMATE]['isActive']) {
				$output['legitimateInterest'] = [
					'value' => true,
					'subscriptionTypeId' => $data[self::HUBSPOT_CONSENT_LEGITIMATE]['typeId'],
					'legalBasis' => $data[self::HUBSPOT_CONSENT_LEGITIMATE]['basis'],
					'text' => $data[self::HUBSPOT_CONSENT_LEGITIMATE]['text'],
				];
			} else {
				if ($typeCustom === self::HUBSPOT_CONSENT_PROCESSING) {
					$output['consent'] = [
						'consentToProcess' => !!$value,
						'text' => $value,
					];
				}

				if ($typeCustom === self::HUBSPOT_CONSENT_COMMUNICATION) {
					$communicationOutput[] = [
						'value' => !!$value,
						'subscriptionTypeId' => \end($name),
						'text' => \array_values(\array_filter(
							$data[self::HUBSPOT_CONSENT_COMMUNICATION]['items'],
							static function ($item) use ($name) {
								return $item['id'] === \end($name);
							}
						))[0]['label'],
					];
				}
			}
		}

		if ($communicationOutput) {
			$output['consent']['communications'] = $communicationOutput;
		}

		return $output;
	}

	/**
	 * Prepare params
	 *
	 * @param array<string, mixed> $params Params.
	 * @param string $formId FormId value.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function prepareParams(array $params, string $formId): array
	{
		$output = [];

		// Map enrichment data.
		$params = $this->enrichment->mapEnrichmentFields($params);

		// Filter params.
		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsHubspot::SETTINGS_TYPE_KEY, 'prePostParams']);
		if (\has_filter($filterName)) {
			$params = \apply_filters($filterName, $params, $formId) ?? [];
		}

		// Remove unecesery params.
		$params = UtilsGeneralHelper::removeUneceseryParamFields($params);

		foreach ($params as $param) {
			$typeCustom = $param['typeCustom'] ?? '';

			// Remove consent data.
			if (
				$typeCustom === self::HUBSPOT_CONSENT_COMMUNICATION ||
				$typeCustom === self::HUBSPOT_CONSENT_LEGITIMATE ||
				$typeCustom === self::HUBSPOT_CONSENT_PROCESSING
			) {
				continue;
			}

			$type = $param['type'] ?? '';

			$value = $param['value'] ?? '';
			if (!$value) {
				continue;
			}

			$name = $param['name'] ?? '';
			if (!$name) {
				continue;
			}

			if ($type === 'checkbox') {
				if ($value === 'on') {
					$value = 'true';
				}

				$value = \str_replace(UtilsConfig::DELIMITER, ';', $value);
			}

			// Must be in UTC timestamp with milliseconds.
			if ($type === 'date') {
				$value = \strtotime($value) * 1000;
			}

			$output[] = [
				'name' => $name,
				'value' => $value,
				'objectTypeId' => $param['custom'] ?? '',
			];
		}

		return $output;
	}

	/**
	 * Prepare files.
	 *
	 * @param array<string, mixed> $files Files.
	 * @param string $formId FormId value.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function prepareFiles(array $files, string $formId): array
	{
		$output = [];

		foreach ($files as $items) {
			if (!$items) {
				continue;
			}

			$name = $items['name'] ?? '';
			$value = $items['value'] ?? [];

			if (!$name || !$value) {
				continue;
			}

			foreach ($value as $file) {
				$fileUrl = $this->postFileMedia($file, $formId);

				if (!$fileUrl) {
					continue;
				}

				$output[] = [
					'name' => $name,
					'value' => $fileUrl,
					'objectTypeId' => $items['custom'] ?? '',
				];
			}
		}

		return $output;
	}

	/**
	 * Return Api Key from settings or global vairaible.
	 *
	 * @return string
	 */
	private function getApiKey(): string
	{
		return UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getApiKeyHubspot(), SettingsHubspot::SETTINGS_HUBSPOT_API_KEY_KEY)['value'];
	}

	/**
	 * Return HubSpot base url.
	 *
	 * @param string $path Path to append.
	 * @param bool $legacy If legacy use different url.
	 *
	 * @return string
	 */
	private function getBaseUrl(string $path, bool $legacy = false): string
	{
		$url = 'https://api.hsforms.com';

		if ($legacy) {
			$url = 'https://api.hubapi.com';
		}

		return "{$url}/{$path}";
	}
}
