<?php

/**
 * Workable Client integration class.
 *
 * @package EightshiftForms\Integrations\Workable
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Workable;

use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Geolocation\SettingsGeolocation;
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\UploadHelpers;
use EightshiftForms\Helpers\SettingsHelpers;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\DeveloperHelpers;
use EightshiftForms\Helpers\HooksHelpers;

/**
 * WorkableClient integration class.
 */
class WorkableClient implements ClientInterface
{
	/**
	 * Transient cache name for items.
	 */
	public const CACHE_WORKABLE_ITEMS_TRANSIENT_NAME = 'es_workable_items_cache';

	/**
	 * Return items.
	 *
	 * @param bool $hideUpdateTime Determine if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getItems(bool $hideUpdateTime = true): array
	{
		$output = \get_transient(self::CACHE_WORKABLE_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Prevent cache.
		if (DeveloperHelpers::isDeveloperSkipCacheActive()) {
			$output = [];
		}

		// Check if form exists in cache.
		if (!$output) {
			$items = $this->getWorkableItems();

			if ($items) {
				foreach ($items as $item) {
					$id = $item['shortcode'] ?? '';

					if (!$id) {
						continue;
					}

					$output[$id] = [
						'id' => (string) $id,
						'title' => $item['title'] ?? '',
						'fields' => [],
					];
				}

				$output[ClientInterface::TRANSIENT_STORED_TIME] = [
					'id' => ClientInterface::TRANSIENT_STORED_TIME,
					'title' => \current_datetime()->format('Y-m-d H:i:s'),
				];

				\set_transient(self::CACHE_WORKABLE_ITEMS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
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
			$items = $this->getWorkableItem($itemId);

			if ($items) {
				$fields = $items['form_fields'] ?? [];
				$questions = $items['questions'] ?? [];

				$output[$itemId]['fields'] = \array_merge($fields, $questions);

				\set_transient(self::CACHE_WORKABLE_ITEMS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
			}
		}

		return $output[$itemId] ?? [];
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
		$itemId = $formDetails[Config::FD_ITEM_ID];
		$params = $formDetails[Config::FD_PARAMS];
		$files = $formDetails[Config::FD_FILES];
		$formId = $formDetails[Config::FD_FORM_ID];
		$country = $formDetails['country'] ?? '';

		// Filter override post request.
		$filterName = HooksHelpers::getFilterName(['integrations', SettingsWorkable::SETTINGS_TYPE_KEY, 'overridePostRequest']);
		if (\has_filter($filterName)) {
			$filterValue = \apply_filters($filterName, [], $itemId, $params, $files, $formId) ?? [];

			if ($filterValue) {
				return $filterValue;
			}
		}

		$paramsPrepared = $this->prepareParams($params);
		$paramsFiles = $this->prepareFiles($files);
		$tags = $this->prepareTags($country);

		$body = [
			'sourced' => false,
			'candidate' => \array_merge_recursive(
				$paramsPrepared,
				$paramsFiles,
				$tags
			),
		];

		$filterName = HooksHelpers::getFilterName(['integrations', SettingsWorkable::SETTINGS_TYPE_KEY, 'prePostId']);
		if (\has_filter($filterName)) {
			$itemId = \apply_filters($filterName, $itemId, $paramsPrepared, $formId) ?? $itemId;
		}

		$url = "{$this->getBaseUrl()}jobs/{$itemId}/candidates";

		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode($body),
			]
		);

		// Structure response details.
		$details = ApiHelpers::getIntegrationApiResponseDetails(
			SettingsWorkable::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$paramsPrepared,
			$paramsFiles,
			$itemId,
			$formId
		);

		$code = $details[Config::IARD_CODE];
		$body = $details[Config::IARD_BODY];

		// On success return output.
		if (ApiHelpers::isSuccessResponse($code)) {
			return ApiHelpers::getIntegrationSuccessInternalOutput($details);
		}

		$details[Config::IARD_VALIDATION] = $this->getFieldsErrors($body);
		$details[Config::IARD_MSG] = $this->getErrorMsg($body);

		// Output error.
		return ApiHelpers::getIntegrationErrorInternalOutput($details);
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
			case 'Bad Request':
				return 'workableBadRequestError';
			case 'position is draft or archived':
				return 'workableArchivedJobError';
			case 'Filename should contain less characters':
				return 'workableTooLongFileNameError';
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
		$msg = $body['error'] ?? '';
		$errors = $body['validation_errors'] ?? [];
		$output = [];

		foreach ($errors as $key => $value) {
			$message = $value[0] ?? '';

			if (!$message) {
				continue;
			}

			switch ($message) {
				case 'can\'t be blank':
					$output[$key] = 'validationRequired';
					break;
				case 'is too long (maximum is 127 characters)':
					$output[$key] = 'validationWorkableMaxLength127';
					break;
				case 'is too long (maximum is 255 characters)':
					$output[$key] = 'validationWorkableMaxLength255';
					break;
				case 'is invalid':
					if ($key === 'email') {
						$output[$key] = 'validationEmail';
					} else {
						$output[$key] = 'validationInvalid';
					}
					break;
			}
		}

		if ($msg === 'either name or firstname and lastname should be part of the candidate\'s payload') {
			$output['firstname'] = 'validationRequired';
			$output['lastname'] = 'validationRequired';
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
		return ApiHelpers::getIntegrationApiResponseDetails(
			SettingsWorkable::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);
	}

	/**
	 * API request to get all jobs from Workable.
	 *
	 * @return array<string, mixed>
	 */
	private function getWorkableItems(): array
	{
		$statuses = \array_filter(\explode(Config::DELIMITER, SettingsHelpers::getOptionValue(SettingsWorkable::SETTINGS_WORKABLE_LIST_TYPE_KEY)));
		$statuses = \array_merge(['published'], $statuses);

		$output = [];

		foreach ($statuses as $status) {
			$url = "{$this->getBaseUrl()}jobs?limit=1000&state={$status}";

			$response = \wp_remote_get(
				$url,
				[
					'headers' => $this->getHeaders(),
				]
			);

			// Structure response details.
			$details = ApiHelpers::getIntegrationApiResponseDetails(
				SettingsWorkable::SETTINGS_TYPE_KEY,
				$response,
				$url,
			);

			$code = $details[Config::IARD_CODE];
			$body = $details[Config::IARD_BODY];

			// On success return output.
			if (ApiHelpers::isSuccessResponse($code)) {
				$output = \array_merge($output, $body['jobs'] ?? []);
			}
		}

		return $output;
	}

	/**
	 * API request to get one job by ID from Workable.
	 *
	 * @param string $jobId Job id to search.
	 *
	 * @return array<string, mixed>
	 */
	private function getWorkableItem(string $jobId)
	{
		$url = "{$this->getBaseUrl()}jobs/{$jobId}/application_form";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		$details = ApiHelpers::getIntegrationApiResponseDetails(
			SettingsWorkable::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details[Config::IARD_CODE];
		$body = $details[Config::IARD_BODY];



		// On success return output.
		if (ApiHelpers::isSuccessResponse($code)) {
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
		$output = [];
		$answers = [];

		// Remove unnecessary params.
		$params = GeneralHelpers::removeUnnecessaryParamFields($params);

		foreach ($params as $param) {
			$name = $param['name'] ?? '';
			if (!$name) {
				continue;
			}

			$value = $param['value'] ?? '';
			$typeCustom = $param['typeCustom'] ?? '';

			// Skip empty check if bool.
			if ($typeCustom !== 'boolean') {
				if (!$value) {
					continue;
				}
			}

			if (!$value) {
				continue;
			}

			switch ($typeCustom) {
				case 'free_text':
				case 'short_text':
					if ($name === 'summary' || $name === 'cover_letter') {
						$output[$name] = $value;
						break;
					}

					$answers[] = [
						'question_key' => $name,
						'body' => $value,
					];
					break;
				case 'boolean':
					if (isset($value[0])) {
						$answers[] = [
							'question_key' => $name,
							'checked' => \filter_var($value[0], \FILTER_VALIDATE_BOOLEAN),
						];
					}
					break;
				case 'multiple_choice':
				case 'dropdown':
					$answers[] = [
						'question_key' => $name,
						'choices' => $value,
					];
					break;
			}

			$output[$name] = $value;
		}

		if ($answers) {
			$output['answers'] = $answers;
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
		$answers = [];

		foreach ($files as $items) {
			$name = $items['name'] ?? '';
			if (!$name) {
				continue;
			}

			$value = $items['value'] ?? [];
			if (!$value) {
				continue;
			}

			foreach ($value as $file) {
				$fileName = UploadHelpers::getFileNameFromPath($file);

				if ($name === 'resume') {
					$output[$name] = [
						'name' => $fileName,
						// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
						'data' => \base64_encode(\file_get_contents($file)),
						// phpcs:enable
					];
				} else {
					$answers[] = [
						'question_key' => $name,
						'file' => [
							'name' => $fileName,
							// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
							'data' => \base64_encode(\file_get_contents($file)),
							// phpcs:enable
						]
					];
				}
			}
		}

		if ($answers) {
			$output['answers'] = $answers;
		}

		return $output;
	}

	/**
	 * Return Subdomain from settings or global variable.
	 *
	 * @return string
	 */
	private function getSubdomain(): string
	{
		return SettingsHelpers::getOptionWithConstant(Variables::getSubdomainWorkable(), SettingsWorkable::SETTINGS_WORKABLE_SUBDOMAIN_KEY);
	}

	/**
	 * Return Api Key from settings or global variable.
	 *
	 * @return string
	 */
	private function getApiKey(): string
	{
		return SettingsHelpers::getOptionWithConstant(Variables::getApiKeyWorkable(), SettingsWorkable::SETTINGS_WORKABLE_API_KEY_KEY);
	}

	/**
	 * Return base url.
	 *
	 * @return string
	 */
	private function getBaseUrl(): string
	{
		$server = $this->getSubdomain();

		return "https://{$server}.workable.com/spi/v3/";
	}

	/**
	 * Prepare tags.
	 *
	 * @param string $country Country.
	 *
	 * @return array<string, mixed>
	 */
	private function prepareTags(string $country): array
	{
		if (!$country) {
			return [];
		}

		$isGeolocationEnabled = SettingsHelpers::isOptionCheckboxChecked(SettingsGeolocation::SETTINGS_GEOLOCATION_USE_KEY, SettingsGeolocation::SETTINGS_GEOLOCATION_USE_KEY);

		if (!$isGeolocationEnabled) {
			return [];
		}

		$tags = SettingsHelpers::getOptionValueGroup(SettingsWorkable::SETTINGS_WORKABLE_GEOLOCATION_TAGS_KEY);

		if (!$tags) {
			return [];
		}

		$output = [];

		foreach ($tags as $tag) {
			$code = $tag[0] ?? '';
			$value = $tag[1] ?? '';

			if (!$code || !$value) {
				continue;
			}

			if (\strtolower($code) !== \strtolower($country)) {
				continue;
			}

			$values = \explode(',', $value);

			foreach ($values as $value) {
				$output[] = \trim($value);
			}
		}

		if (!$output) {
			return [];
		}

		return ['tags' => $output];
	}
}
