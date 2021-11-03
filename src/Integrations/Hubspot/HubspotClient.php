<?php

/**
 * HubSpot Client integration class.
 *
 * @package EightshiftForms\Integrations\Hubspot
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Hubspot;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;

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
	 * Transient cache name for simple jobs.
	 */
	public const CACHE_HUBSPOT_JOBS_TRANSIENT_NAME = 'es_hubspot_jobs_cache';

	/**
	 * Transient cache name for jobs questions.
	 */
	public const CACHE_HUBSPOT_JOBS_QUESTIONS_TRANSIENT_NAME = 'es_hubspot_jobs_questions_cache';

	/**
	 * Return jobs simple list from Hubspot.
	 *
	 * @return array<string, mixed>
	 */
	public function getForms(): array
	{
		$output = get_transient(self::CACHE_HUBSPOT_JOBS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Check if form exists in cache.
		if (empty($output)) {
			$forms = $this->getHubspotForms();

			if ($forms) {
				foreach ($forms as $form) {
					$id = $form['guid'] ?? '';

					if (!$id) {
						continue;
					}

					$fields = $form['formFieldGroups'] ?? [];

					// Find and populate consent data.
					$consentData = $this->getConsentData($form);

					if ($consentData) {
						$fields = array_merge($fields, $consentData);
					}

					$portalId = $form['portalId'] ?? '';
					$value = "{$id}---{$portalId}";

					$output[$value] = [
						'id' => $value,
						'title' => $form['name'] ?? '',
						'fields' => $fields,
					];
				}

				set_transient(self::CACHE_HUBSPOT_JOBS_TRANSIENT_NAME, $output, 3600);
			}
		}

		return $output;
	}

	/**
	 * Return form with cache option for faster loading.
	 *
	 * @param string $formId Form id to search by.
	 *
	 * @return array<string, mixed>
	 */
	public function getForm(string $formId): array
	{
		$output = get_transient(self::CACHE_HUBSPOT_JOBS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Check if form exists in cache.
		if (empty($output) || !isset($output[$formId]) || empty($output[$formId])) {
			$output = $this->getForms();
		}

		return $output[$formId] ?? [];
	}

	/**
	 * API request to post form application to Hubspot.
	 *
	 * @param string $formId Form id to search.
	 * @param array<string, mixed>  $params Params array.
	 * @param array<string, mixed>  $files Files array.
	 *
	 * @return array<string, mixed>
	 */
	public function postHubspotApplication(string $formId, array $params, array $files): array
	{
		$formId = explode('---', $formId);

		$consent = array_filter(
			$params,
			function($item) {
				$name = $item['name'] ?? '';
				return strpos($name, 'CONSENT_') === 0;
			}
		);

		$outputConsent = [];

		if ($consent) {
			$outputConsent['consent'] = [];

			foreach($consent as $key => $value) {
				$name = explode('.', $value['name']);
				$type = $name[0];
				$id = $name[1] ?? '';

				if ($type === 'CONSENT_PROCESSING') {
					$outputConsent['consent']['consentToProcess'] = true;
					$outputConsent['consent']['text'] = $value['value'];
				}

				if ($type === 'CONSENT_COMMUNICATION') {
					if ($value['value']) {
						$outputConsent['consent']['communications'][] = [
							'value' => true,
							'subscriptionTypeId' => $id,
							'text' => $value['value'],
						];
					}
				}

				unset($params[$key]);
			}
		}

		$body = [
			'fields' => $this->prepareParams($params),
			'context' => [
				'hutk' => $params['es-form-hubspot-cookie']['value'],
				'pageUri' => $params['es-form-hubspot-page-url']['value'],
				'pageName' => $params['es-form-hubspot-page-name']['value'],
			],
			'legalConsentOptions' => $outputConsent,
		];

		$response = \wp_remote_post(
			$this->getBaseUrl("submissions/v3/integration/secure/submit/{$formId[1]}/{$formId[0]}"),
			[
				'headers' => $this->getHeaders(true),
				'body' => wp_json_encode($body),
			]
		);

		$status = $response['response']['status'] ?? 200;
		$code = $response['response']['code'] ?? '';

		if ($status === 200 && empty($code)) {
			return $response['response'];
		}

		return json_decode(\wp_remote_retrieve_body($response), true) ?? [];
	}

	/**
	 * API request to get all forms from Hubspot.
	 *
	 * @return array<string, mixed>
	 */
	private function getHubspotForms()
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
	 * @param boolean $useAuth If using post method we need to send Authorization header in the request.
	 *
	 * @return array<string, mixed>
	 */
	private function getHeaders(): array
	{
		$headers = [
			'Content-Type' => 'application/json; charset=utf-8',
		];

		return $headers;
	}

	/**
	 * Populate and prepare consent checkboxes.
	 *
	 * @param array<string, mixed> $form Form data got from the api.
	 *
	 * @return array<string, mixed>
	 */
	private function getConsentData(array $form): array
	{
		$output = [];

		// Find consent data from meta.
		$consentData = array_filter(
			$form['metaData'],
			function($item) {
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
				$name = $consentData[0]['name'] ?? '';

				$consentCommunicationOptions = [];
				foreach ($communicationConsentCheckboxes as $key => $value) {
					$communicationTypeId = $value['communicationTypeId'] ?? '';
					$consentCommunicationOptions[] = [
						'name' => "CONSENT_COMMUNICATION.{$communicationTypeId}",
						'id' => "CONSENT_COMMUNICATION.{$communicationTypeId}",
						'label' => $value['label'] ?? '',
						'required' => $value['required'] ?? false,
					];
				}

				$output[]['fields'][0] = [
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
								'name' => "CONSENT_PROCESSING",
								'id' => "CONSENT_PROCESSING",
								'required' => true,
								'communicationTypeId' => '', // Empty on purpose.
							]
						];
					}

					$output[]['fields'][0] = [
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
	 * @param array<string, mixed>  $params Params.
	 *
	 * @return array<string, mixed>
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
