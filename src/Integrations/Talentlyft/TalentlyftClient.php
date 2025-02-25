<?php

/**
 * Talentlyft Client integration class.
 *
 * @package EightshiftForms\Integrations\Talentlyft
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Talentlyft;

use EightshiftForms\Cache\SettingsCache;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsUploadHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;

/**
 * TalentlyftClient integration class.
 */
class TalentlyftClient implements ClientInterface
{
	/**
	 * Transient cache name for items.
	 */
	public const CACHE_TALENTLYFT_ITEMS_TRANSIENT_NAME = 'es_talentlyft_items_cache';

	/**
	 * Return items.
	 *
	 * @param bool $hideUpdateTime Determine if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getItems(bool $hideUpdateTime = true): array
	{
		$output = \get_transient(self::CACHE_TALENTLYFT_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Prevent cache.
		if (UtilsDeveloperHelper::isDeveloperSkipCacheActive()) {
			$output = [];
		}

		// Check if form exists in cache.
		if (!$output) {
			$items = $this->getTalentlyftItems();

			if ($items) {
				foreach ($items as $item) {
					$id = $item['Id'] ?? '';

					if (!$id) {
						continue;
					}

					$output[$id] = [
						'id' => (string) $id,
						'title' => $item['Title'] ?? '',
						'fields' => [],
					];
				}

				$output[ClientInterface::TRANSIENT_STORED_TIME] = [
					'id' => ClientInterface::TRANSIENT_STORED_TIME,
					'title' => \current_datetime()->format('Y-m-d H:i:s'),
				];

				\set_transient(self::CACHE_TALENTLYFT_ITEMS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
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
		$output = $this->getItems();

		$item = $output[$itemId]['fields'] ?? [];

		// Check if form exists in cache.
		if (!$output || !$item) {
			$items = $this->getTalentlyftItem($itemId);

			if ($items) {
				$fields = $items['FormFields'] ?? [];
				$questions = $items['Questions'] ?? [];
				$customFields = $items['CustomFields'] ?? [];

				$output[$itemId]['fields'] = \array_merge($fields, $questions, $customFields);

				\set_transient(self::CACHE_TALENTLYFT_ITEMS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
			}
		}

		return $output[$itemId] ?? [];
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
		$paramsPrepared = $this->prepareParams($params);
		$paramsFiles = $this->prepareFiles($files);

		$body = \array_merge_recursive(
			[
				'JobId' => $itemId,
				'Applied' => UtilsSettingsHelper::isSettingCheckboxChecked(SettingsTalentlyft::SETTINGS_TALENTLYFT_USE_FLAGS_APPLIED_KEY, SettingsTalentlyft::SETTINGS_TALENTLYFT_USE_FLAGS_KEY, $formId),
				'IsProspect' => UtilsSettingsHelper::isSettingCheckboxChecked(SettingsTalentlyft::SETTINGS_TALENTLYFT_USE_FLAGS_PROSPECT_KEY, SettingsTalentlyft::SETTINGS_TALENTLYFT_USE_FLAGS_KEY, $formId),
			],
			$paramsPrepared,
			$paramsFiles,
		);

		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsTalentlyft::SETTINGS_TYPE_KEY, 'prePostId']);
		if (\has_filter($filterName)) {
			$itemId = \apply_filters($filterName, $itemId, $paramsPrepared, $formId) ?? $itemId;
		}

		$url = "{$this->getBaseUrl()}candidates";

		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode($body),
			]
		);

		// Structure response details.
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsTalentlyft::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$paramsPrepared,
			$paramsFiles,
			$itemId,
			$formId,
			UtilsSettingsHelper::isOptionCheckboxChecked(SettingsTalentlyft::SETTINGS_TALENTLYFT_SKIP_INTEGRATION_KEY, SettingsTalentlyft::SETTINGS_TALENTLYFT_SKIP_INTEGRATION_KEY)
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
		$msg = $body['Message'] ?? '';

		switch ($msg) {
			case 'An error has occurred':
				return 'talentlyftBadRequestError';
			case 'Validation Failed':
				return 'talentlyftValicationError';
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
		$errors = $body['Errors'] ?? [];
		$output = [];

		foreach ($errors as $error) {
			$field = $error['Field'] ?? '';
			$message = $error['Message'] ?? '';

			if (!$message || !$field) {
				continue;
			}

			// Validate req fields.
			\preg_match_all("/(The )(\w*)( field is required.)/", $message, $matchesReq, \PREG_SET_ORDER, 0);

			if ($matchesReq) {
				$key = $matchesReq[0][2] ?? '';
				if ($key) {
					$output["q_{$key}"] = 'validationRequired';
				}
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
		$url = "{$this->getBaseUrl()}jobs?limit=1";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		return UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsTalentlyft::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);
	}
	/**
	 * API request to get all jobs from Talentlyft.
	 *
	 * @return array<string, mixed>
	 */
	private function getTalentlyftItems(): array
	{
		$statuses = \array_filter(\explode(UtilsConfig::DELIMITER, UtilsSettingsHelper::getOptionValue(SettingsTalentlyft::SETTINGS_TALENTLYFT_LIST_TYPE_KEY)));

		\array_unshift($statuses, 'published');

		$output = [];

		foreach ($statuses as $status) {
			$url = "{$this->getBaseUrl()}jobs?perPage=200&status={$status}"; // API limit is 200.

			$response = \wp_remote_get(
				$url,
				[
					'headers' => $this->getHeaders(),
				]
			);

			// Structure response details.
			$details = UtilsApiHelper::getIntegrationApiReponseDetails(
				SettingsTalentlyft::SETTINGS_TYPE_KEY,
				$response,
				$url,
			);

			$code = $details[UtilsConfig::IARD_CODE];
			$body = $details[UtilsConfig::IARD_BODY];

			UtilsDeveloperHelper::setQmLogsOutput($details);

			// On success return output.
			if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
				$output = \array_merge($output, $body['Results'] ?? []);
			}
		}

		return $output;
	}

	/**
	 * API request to get one job by ID from Talentlyft.
	 *
	 * @param string $jobId Job id to search.
	 *
	 * @return array<string, mixed>
	 */
	private function getTalentlyftItem(string $jobId)
	{
		$url = "{$this->getBaseUrl()}jobs/{$jobId}/form";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsTalentlyft::SETTINGS_TYPE_KEY,
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
	 * Set headers used for fetching data.
	 *
	 * @return array<int|string, string>
	 */
	private function getHeaders(): array
	{
		return [
			'Authorization' => 'Bearer ' . $this->getApiKey(),
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
		];
	}

	/**
	 * Prepare params
	 *
	 * @param array<string, mixed> $params Params.
	 *
	 * @return array<string, mixed>
	 */
	private function prepareParams(array $params): array
	{
		// Remove unecesery params.
		$params = UtilsGeneralHelper::removeUneceseryParamFields($params);

		$output = [];
		$outputCustom = [];

		foreach ($params as $param) {
			$name = $param['name'] ?? '';
			if (!$name) {
				continue;
			}

			$value = $param['value'] ?? '';
			$type = $param['type'] ?? '';
			$typeCustom = $param['typeCustom'] ?? '';

			if (!$value) {
				continue;
			}

			$name = \preg_replace('/^q_/', '', $name);

			switch ($typeCustom) {
				case 'customField':
					if (\in_array($type, ['radio', 'select', 'checkbox'], true)) {
						$outputCustom[] = [
							'Id' => (int) $name,
							'Choices' => $value
						];
					} else {
						$outputCustom[] = [
							'Id' => (int) $name,
							'Body' => $value,
						];
					}
					break;
				case 'address':
					$output[$name] = [
						'address' => $value,
					];
					break;
				default:
					$output[$name] = $value;
					break;
			}
		}

		if ($outputCustom) {
			// Due to poor API design we need to send custom fields in two different ways.
			$output['CustomFieldAnswers'] = $outputCustom;
			$output['Answers'] = $outputCustom;
		}

		return $output;
	}

	/**
	 * Prepare files.
	 *
	 * @param array<string, mixed> $files Files.
	 *
	 * @return array<string, mixed>
	 */
	private function prepareFiles(array $files): array
	{
		$output = [];
		$outputCustom = [];

		foreach ($files as $items) {
			$name = $items['name'] ?? '';
			if (!$name) {
				continue;
			}

			$value = $items['value'] ?? [];
			if (!$value) {
				continue;
			}

			$typeCustom = $items['typeCustom'] ?? '';

			$name = \preg_replace('/^q_/', '', $name);

			foreach ($value as $file) {
				$fileName = UtilsUploadHelper::getFileNameFromPath($file);

				switch ($typeCustom) {
					case 'customField':
						$outputCustom[] = [
							'Id' => (int) $name,
							'File' => [
								'FileName' => $fileName,
								// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
								'Content' => \base64_encode(\file_get_contents($file)),
								// phpcs:enable
							],
						];
						break;
					default:
						$output[$name] = [
							'FileName' => $fileName,
							// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
							'Content' => \base64_encode(\file_get_contents($file)),
							// phpcs:enable
						];
						break;
				}
			}
		}

		if ($outputCustom) {
			// Due to poor API design we need to send custom fields in two different ways.
			$output['CustomFieldAnswers'] = $outputCustom;
			$output['Answers'] = $outputCustom;
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
		return UtilsSettingsHelper::getSettingsDisabledOutputWithDebugFilter(Variables::getApiKeyTalentlyft(), SettingsTalentlyft::SETTINGS_TALENTLYFT_API_KEY_KEY)['value'];
	}

	/**
	 * Return base url.
	 *
	 * @return string
	 */
	private function getBaseUrl(): string
	{
		return "https://api.talentlyft.com/v2/";
	}
}
