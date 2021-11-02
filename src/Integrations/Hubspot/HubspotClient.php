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
					$guid = $form['guid'] ?? '';

					if (!$guid) {
						continue;
					}

					$fields = $form['formFieldGroups'] ?? [];

					// Find and populate consent data.
					$consentData = $this->getConsentData($form);

					if ($consentData) {
						$fields = array_merge($fields, $consentData);
					}

					$output[$guid] = [
						'id' => $guid,
						'portalId' => $form['portalId'] ?? '',
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

		return $output[$formId];
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
				$output[]['fields'][0] = [
					'name' => $consentData[0]['name'] ?? '',
					'options' => $communicationConsentCheckboxes,
					'enabled' => true,
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
								'communicationTypeId' => '',
							]
						];
					}

					$output[]['fields'][0] = [
						'name' => $consentData[0]['name'] ?? '',
						'options' => $consentProcessingOptions,
						'enabled' => true,
						'fieldType' => 'consent',
						'beforeText' => $processingConsentText,
					];
				}
			}

			// Populate checbox for legal text.
			if ($privacyPolicyText) {
				$consentLegal['fields'][0] = [
					'name' => $consentData[0]['name'] ?? '',
					'options' => [],
					'enabled' => true,
					'fieldType' => 'consent',
					'beforeText' => $privacyPolicyText,
				];

				$output[] = $consentLegal;
			}
		}

		return $output;
	}

	/**
	 * API request to post job application to Hubspot.
	 *
	 * @param string $jobId Job id to search.
	 * @param array<string, mixed>  $params Params array.
	 * @param array<string, mixed>  $files Files array.
	 *
	 * @return array<string, mixed>
	 */
	// public function postHubspotApplication(string $jobId, array $params, array $files): array
	// {
	// 	$response = \wp_remote_post(
	// 		"{$this->getJobBoardUrl()}boards/{$this->getBoardToken()}/jobs/{$jobId}",
	// 		[
	// 			'headers' => $this->getHeaders(true),
	// 			'body' => wp_json_encode(
	// 				array_merge(
	// 					$this->prepareParams($params),
	// 					$this->prepareFiles($files)
	// 				)
	// 			),
	// 		]
	// 	);

	// 	return json_decode(\wp_remote_retrieve_body($response), true) ?? [];
	// }

	/**
	 * API request to get all forms from Hubspot.
	 *
	 * @return array<string, mixed>
	 */
	private function getHubspotForms()
	{
		$response = \wp_remote_get(
			$this->getBaseUrl('forms/v2/forms'),
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
	 * Prepare params
	 *
	 * @param array<string, mixed>  $params Params.
	 *
	 * @return array<string, mixed>
	 */
	// private function prepareParams(array $params): array
	// {
	// 	$output = [];

	// 	foreach ($params as $key => $value) {
	// 		$output[$key] = $value['value'] ?? '';
	// 	}

	// 	return $output;
	// }

	/**
	 * Prepare files.
	 *
	 * @param array<string, mixed>  $files Files.
	 *
	 * @return array<string, mixed>
	 */
	// private function prepareFiles(array $files): array
	// {
	// 	$output = [];

	// 	foreach ($files as $key => $value) {
	// 		$name = explode('-', $key);
	// 		$fileName = explode('/', $value);

	// 		$output["{$name[0]}_content"] = base64_encode((string) file_get_contents($value)); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	// 		$output["{$name[0]}_content_filename"] = end($fileName);
	// 	}

	// 	return $output;
	// }

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
	private function getBaseUrl(string $path): string
	{
		return "https://api.hubapi.com/{$path}?hapikey={$this->getApiKey()}";
	}
}
