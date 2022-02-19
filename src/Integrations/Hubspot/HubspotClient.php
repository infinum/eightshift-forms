<?php

/**
 * HubSpot Client integration class.
 *
 * @package EightshiftForms\Integrations\Hubspot
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Hubspot;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;

/**
 * HubspotClient integration class.
 */
class HubspotClient implements ClientInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Transient cache name for items.
	 */
	public const CACHE_HUBSPOT_ITEMS_TRANSIENT_NAME = 'es_hubspot_items_cache';

	/**
	 * Return items.
	 *
	 * @return array<string, mixed>
	 */
	public function getItems(): array
	{
		$output = get_transient(self::CACHE_HUBSPOT_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Check if form exists in cache.
		if (empty($output)) {
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
						$fields = array_merge($fields, $consentData);
					}

					$portalId = $item['portalId'] ?? '';
					$value = "{$id}---{$portalId}";

					$output[$value] = [
						'id' => $value,
						'title' => $item['name'] ?? '',
						'fields' => $fields,
					];
				}

				set_transient(self::CACHE_HUBSPOT_ITEMS_TRANSIENT_NAME, $output, 3600);
			}
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
		$output = get_transient(self::CACHE_HUBSPOT_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Check if form exists in cache.
		if (empty($output) || !isset($output[$itemId]) || empty($output[$itemId])) {
			$output = $this->getItems();
		}

		return $output[$itemId] ?? [];
	}

	/**
	 * API request to post application.
	 *
	 * @param string $itemId Item id to search.
	 * @param array<string, mixed> $params Params array.
	 * @param array<string, array<int, array<string, mixed>>> $files Files array.
	 *
	 * @return array<string, mixed>
	 */
	public function postApplication(string $itemId, array $params, array $files): array
	{
		$itemId = explode('---', $itemId);

		$consent = array_filter(
			$params,
			static function ($item) {
				$name = $item['name'] ?? '';
				return strpos($name, 'CONSENT_') === 0;
			}
		);

		$outputConsent = [];

		$body = [
			'context' => [
				'ipAddress' => isset($_SERVER['REMOTE_ADDR']) ? \sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				'hutk' => $params['es-form-hubspot-cookie']['value'],
				'pageUri' => $params['es-form-hubspot-page-url']['value'],
				'pageName' => $params['es-form-hubspot-page-name']['value'],
			],
		];

		if ($consent) {
			$outputConsent = [];

			foreach ($consent as $key => $value) {
				$name = explode('.', $value['name']);
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

		$body['fields'] = array_merge(
			$this->prepareParams($params),
			$this->prepareFiles($files)
		);

		$response = \wp_remote_post(
			$this->getBaseUrl("submissions/v3/integration/secure/submit/{$itemId[1]}/{$itemId[0]}"),
			[
				'headers' => $this->getHeaders(),
				'body' => wp_json_encode($body),
			]
		);

		if (is_wp_error($response)) {
			return [
				'status' => 'error',
				'code' => 400,
				'message' => $this->getErrorMsg('submitWpError'),
			];
		}

		$code = $response['response']['code'] ?? 200;

		if ($code === 200) {
			return [
				'status' => 'success',
				'code' => $code,
				'message' => 'hubspotSuccess',
			];
		}

		$responseBody = json_decode(\wp_remote_retrieve_body($response), true);
		$responseMessage = $responseBody['message'] ?? '';
		$responseErrors = $responseBody['errors'] ?? [];

		$output = [
			'status' => 'error',
			'code' => $code,
			'message' => $this->getErrorMsg($responseMessage, $responseErrors),
		];

		Helper::logger([
			'integration' => 'hubspot',
			'body' => $body,
			'response' => $response['response'],
			'responseBody' => $responseBody,
			'output' => $output,
		]);

		return $output;
	}

	/**
	 * Get post file media sent to HubSpot file manager.
	 *
	 * @param array<string> $file File to send.
	 *
	 * @return string
	 */
	private function postFileMedia(array $file): string
	{
		if (!$file) {
			return '';
		}

		$path = $file['path'] ?? '';

		if (!$path) {
			return '';
		}

		$options = [
			'folderPath' => '/esforms',
			'options' => wp_json_encode([
				"access" => "PUBLIC_NOT_INDEXABLE",
				"overwrite" => false,
			]),
		];

		$filterName = Filters::getIntegrationFilterName(SettingsHubspot::SETTINGS_TYPE_KEY, 'filesOptions');
		if (has_filter($filterName)) {
			$options = \apply_filters($filterName, []);
		}

		$postData = array_merge(
			[
				'file' => new \CURLFile($path, 'application/octet-stream'),
			],
			$options
		);

		$curl = curl_init(); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_init
		curl_setopt_array( // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt_array
			$curl,
			[
				CURLOPT_URL => $this->getBaseUrl("filemanager/api/v3/files/upload", true),
				CURLOPT_FAILONERROR => true,
				CURLOPT_POST => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POSTFIELDS => $postData
			]
		);

		$response = curl_exec($curl); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_exec
		$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_getinfo
		curl_close($curl); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_close

		if ($statusCode === 200) {
			$response = json_decode((string) $response, true);

			return $response['objects'][0]['url'] ?? '';
		}

		return '';
	}


	/**
	 * Map service messages with our own.
	 *
	 * @param string $msg Message got from the API.
	 * @param array<string, mixed> $errors Additional errors got from the API.
	 *
	 * @return string
	 */
	private function getErrorMsg(string $msg, array $errors = []): string
	{
		if ($errors) {
			$invalidEmail = array_filter(
				$errors,
				static function ($error) {
					return $error['errorType'] === 'INVALID_EMAIL';
				}
			);

			if ($invalidEmail) {
				$msg = 'INVALID_EMAIL';
			}

			$requiredField = array_filter(
				$errors,
				static function ($error) {
					return $error['errorType'] === 'REQUIRED_FIELD';
				}
			);

			if ($requiredField) {
				$msg = 'REQUIRED_FIELD';
			}
		}

		switch ($msg) {
			case 'Bad Request':
				return 'hubspotBadRequestError';
			case 'The request is not valid':
				return 'hubspotInvalidRequestError';
			case 'INVALID_EMAIL':
				return 'hubspotInvalidEmailError';
			case 'REQUIRED_FIELD':
				return 'hubspotMissingFieldsError';
			default:
				return 'submitWpError';
		}
	}

	/**
	 * API request to get all items from Hubspot.
	 *
	 * @return array<string, mixed>
	 */
	private function getHubspotItems()
	{
		$response = \wp_remote_get(
			$this->getBaseUrl('forms/v2/forms', true),
			[
				'headers' => $this->getHeaders(),
				'timeout' => 60,
			]
		);

		return json_decode(\wp_remote_retrieve_body($response), true);
	}

	/**
	 * Set headers used for fetching data.
	 *
	 * @return array<string, mixed>
	 */
	private function getHeaders(): array
	{
		return [
			'Content-Type' => 'application/json; charset=utf-8',
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
		$consentData = array_filter(
			$item['metaData'],
			static function ($item) {
				return $item['name'] === 'legalConsentOptions';
			}
		);

		// Check for consent data.
		if ($consentData) {
			$consentData = array_values($consentData);

			// Decode consent data.
			$consentOptions = json_decode($consentData[0]['value'], true);

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
								'label' => wp_strip_all_tags($processingConsentCheckboxLabel),
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

		unset($params['es-form-hubspot-cookie']);
		unset($params['es-form-hubspot-page-name']);
		unset($params['es-form-hubspot-page-url']);

		foreach ($params as $value) {
			$output[] = [
				'name' => $value['name'] ?? '',
				'value' => $value['value'] ?? '',
			];
		}

		return $output;
	}

	/**
	 * Prepare files.
	 *
	 * @param array<string, mixed> $files Files.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function prepareFiles(array $files): array
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

				$fileUrl = $this->postFileMedia($file);

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

		return $apiKey ?? $this->getOptionValue(SettingsHubspot::SETTINGS_HUBSPOT_API_KEY_KEY); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
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

		return "{$url}/{$path}?hapikey={$this->getApiKey()}";
	}
}
