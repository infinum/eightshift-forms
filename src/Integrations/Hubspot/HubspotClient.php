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
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Rest\ApiHelper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Validation\Validator;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

/**
 * HubspotClient integration class.
 */
class HubspotClient implements HubspotClientInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Use API helper trait.
	 */
	use ApiHelper;

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
		$output = \get_transient(self::CACHE_HUBSPOT_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

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

					// Find and populate consent data.
					$consentData = $this->getConsentData($item);

					if ($consentData) {
						$fields = \array_merge($fields, $consentData);
					}

					$portalId = $item['portalId'] ?? '';
					$delimiter = AbstractBaseRoute::DELIMITER;
					$value = "{$id}{$delimiter}{$portalId}";

					$output[$value] = [
						'id' => $value,
						'title' => $item['name'] ?? '',
						'fields' => $fields,
						'submitButtonText' => $item['submitText'] ?? '',
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
		$itemId = \explode(AbstractBaseRoute::DELIMITER, $itemId);

		$consent = \array_filter(
			$params,
			static function ($item) {
				$name = $item['name'] ?? '';
				return \strpos($name, 'CONSENT_') === 0;
			}
		);

		$outputConsent = [];

		$body = [
			'context' => [
				'ipAddress' => isset($_SERVER['REMOTE_ADDR']) ? \sanitize_text_field(\wp_unslash($_SERVER['REMOTE_ADDR'])) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				'hutk' => $params[AbstractBaseRoute::CUSTOM_FORM_PARAMS['hubspotCookie']]['value'],
				'pageUri' => $params[AbstractBaseRoute::CUSTOM_FORM_PARAMS['hubspotPageUrl']]['value'],
				'pageName' => $params[AbstractBaseRoute::CUSTOM_FORM_PARAMS['hubspotPageName']]['value'],
			],
		];

		if ($consent) {
			$outputConsent = [];

			foreach ($consent as $key => $value) {
				$name = \explode('.', $value['name']);
				$type = $name[0];
				$id = $name[1] ?? '';

				if ($type === 'CONSENT_PROCESSING' && $value['value']) {
					$outputConsent['consentToProcess'] = true;
					$outputConsent['text'] = $value['value'];
				}

				if ($type === 'CONSENT_COMMUNICATION' && $value['value']) {
					$outputConsent['communications'][] = [
						'value' => true,
						'subscriptionTypeId' => $id,
						'text' => $value['value'],
					];
				}

				unset($params[$key]);
			}

			if ($outputConsent) {
				$body['legalConsentOptions']['consent'] = $outputConsent;
			}
		}

		$paramsPrepared = $this->prepareParams($params);
		$paramsFiles = $this->prepareFiles($files, $formId);

		$body['fields'] = \array_merge(
			$paramsPrepared,
			$paramsFiles
		);

		$url = $this->getBaseUrl("submissions/v3/integration/secure/submit/{$itemId[1]}/{$itemId[0]}");

		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode($body),
			]
		);

		// Structure response details.
		$details = $this->getIntegrationApiReponseDetails(
			SettingsHubspot::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$paramsPrepared,
			$paramsFiles,
			\implode(AbstractBaseRoute::DELIMITER, $itemId),
			$formId
		);

		$code = $details['code'];
		$body = $details['body'];

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

		$customFields = \array_flip(Components::flattenArray(AbstractBaseRoute::CUSTOM_FORM_PARAMS));

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
		$details = $this->getIntegrationApiReponseDetails(
			SettingsHubspot::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$body
		);

		$code = $details['code'];
		$body = $details['body'];

		// On success return output.
		if ($code >= 200 && $code <= 299) {
			return $this->getIntegrationApiSuccessOutput($details);
		}

		// Output error.
		return $this->getIntegrationApiErrorOutput(
			$details,
			$this->getErrorMsg($body)
		);
	}

	/**
	 * Get post file media sent to HubSpot file manager.
	 *
	 * @param array<string> $file File to send.
	 * @param string $formId FormId value.
	 *
	 * @return string
	 */
	private function postFileMedia(array $file, string $formId): string
	{
		if (!$file) {
			return '';
		}

		$path = $file['path'] ?? '';

		if (!$path) {
			return '';
		}

		$folder = $this->getSettingsValue(SettingsHubspot::SETTINGS_HUBSPOT_FILEMANAGER_FOLDER_KEY, $formId);

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

		$filterName = Filters::getFilterName(['integrations', SettingsHubspot::SETTINGS_TYPE_KEY, 'filesOptions']);
		if (\has_filter($filterName)) {
			$options = \apply_filters($filterName, []);
		}

		$postData = \array_merge(
			[
				'file' => new CURLFile($path, 'application/octet-stream'),
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
		$statusCode = \curl_getinfo($curl, \CURLINFO_HTTP_CODE); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_getinfo
		\curl_close($curl); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_close

		if ($statusCode === 200) {
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
		$details = $this->getIntegrationApiReponseDetails(
			SettingsHubspot::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details['code'];
		$body = $details['body'];

		// On success return output.
		if ($code >= 200 && $code <= 299) {
			return $body ?? [];
		}

		return [];
	}

	/**
	 * API request to get all items from Hubspot.
	 *
	 * @return array<string, mixed>
	 */
	private function getHubspotItems()
	{
		$url = $this->getBaseUrl('forms/v2/forms', true);

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		$details = $this->getIntegrationApiReponseDetails(
			SettingsHubspot::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details['code'];
		$body = $details['body'];

		// On success return output.
		if ($code >= 200 && $code <= 299) {
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
	 * @return array<int, array<string, array<int, array<string, mixed>>>>
	 */
	private function getConsentData(array $item): array
	{
		$output = [];

		// Find consent data from meta.
		$consentData = \array_filter(
			$item['metaData'],
			static function ($item) {
				return $item['name'] === 'legalConsentOptions';
			}
		);

		// Check for consent data.
		if ($consentData) {
			$consentData = \array_values($consentData);

			// Decode consent data.
			$consentOptions = \json_decode($consentData[0]['value'], true);

			$isLegitimateInterest = $consentOptions['isLegitimateInterest'] ?? false;
			$privacyPolicyText = $consentOptions['privacyPolicyText'] ?? '';
			$communicationConsentCheckboxes = $consentOptions['communicationConsentCheckboxes'] ?? '';
			$communicationConsentText = $consentOptions['communicationConsentText'] ?? '';
			$processingConsentCheckboxLabel = $consentOptions['processingConsentCheckboxLabel'] ?? '';
			$processingConsentType = $consentOptions['processingConsentType'] ?? '';
			$processingConsentText = $consentOptions['processingConsentText'] ?? '';

			if (!$isLegitimateInterest) {
				// Populate checkbox for communication consent.
				$consentCommunicationOptions = [];
				$communicationTypeId = '';

				foreach ($communicationConsentCheckboxes as $key => $value) {
					$communicationTypeId = $value['communicationTypeId'] ?? '';
					$consentCommunicationOptions[] = [
						'label' => $value['label'] ?? '',
						'required' => $value['required'] ?? false,
					];
				}

				$output[]['fields'][0] = [
					'name' => "CONSENT_COMMUNICATION.{$communicationTypeId}",
					'id' => "CONSENT_COMMUNICATION.{$communicationTypeId}",
					'options' => $consentCommunicationOptions,
					'fieldType' => 'consent',
					'beforeText' => $communicationConsentText,
				];

				// Populate checkbox for processing consent.
				if ($processingConsentCheckboxLabel) {
					$consentProcessingOptions = [];

					if ($processingConsentType === 'REQUIRED_CHECKBOX') {
						$consentProcessingOptions = [
							[
								'label' => \wp_strip_all_tags($processingConsentCheckboxLabel),
								'required' => true,
								'communicationTypeId' => '', // Empty on purpose.
							]
						];
					}

					$output[]['fields'][0] = [
						'name' => "CONSENT_PROCESSING",
						'id' => "CONSENT_PROCESSING",
						'options' => $consentProcessingOptions,
						'fieldType' => 'consent',
						'beforeText' => $processingConsentText,
					];
				}
			}

			// Populate checbox for legal text.
			if ($privacyPolicyText) {
				$consentLegal['fields'][0] = [
					'options' => [],
					'fieldType' => 'consent',
					'beforeText' => $privacyPolicyText,
				];

				$output[] = $consentLegal;
			}
		}

		return $output;
	}

	/**
	 * Prepare params
	 *
	 * @param array<string, mixed> $params Params.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function prepareParams(array $params): array
	{
		$output = [];

		// Map enrichment data.
		$params = $this->enrichment->mapEnrichmentFields($params);

		// Remove unecesery params.
		$params = Helper::removeUneceseryParamFields($params);

		$filterName = Filters::getFilterName(['integrations', SettingsHubspot::SETTINGS_TYPE_KEY, 'prePostParams']);
		if (\has_filter($filterName)) {
			$params = \apply_filters($filterName, $params) ?? [];
		}

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

			if ($type === 'checkbox') {
				if ($value === 'on') {
					$value = 'true';
				}

				$value = \str_replace(AbstractBaseRoute::DELIMITER, ';', $value);
			}

			// Must be in UTC timestamp with milliseconds.
			if ($type === 'date') {
				$value = \strtotime($value) * 1000;
			}

			$output[] = [
				'name' => $name,
				'value' => $value,
				'objectTypeId' => $param['objectTypeId'] ?? '',
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

		if (!$files) {
			return [];
		}

		foreach ($files as $items) {
			if (!$items) {
				continue;
			}

			foreach ($items as $file) {
				$id = $file['id'] ?? '';

				$fileUrl = $this->postFileMedia($file, $formId);

				if (!$fileUrl) {
					continue;
				}

				$output[] = [
					'name' => $id,
					'value' => $fileUrl,
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
		$apiKey = Variables::getApiKeyHubspot();

		return $apiKey ? $apiKey : $this->getOptionValue(SettingsHubspot::SETTINGS_HUBSPOT_API_KEY_KEY); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
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
