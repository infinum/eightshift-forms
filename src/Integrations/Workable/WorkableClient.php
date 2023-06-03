<?php

/**
 * Workable Client integration class.
 *
 * @package EightshiftForms\Integrations\Workable
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Workable;

use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Helpers\UploadHelper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Rest\ApiHelper;
use EightshiftForms\Validation\Validator;

/**
 * WorkableClient integration class.
 */
class WorkableClient implements ClientInterface
{
	/**
	 * Use trait Upload_Helper inside class.
	 */
	use UploadHelper;

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
	public const CACHE_WORKABLE_ITEMS_TRANSIENT_NAME = 'es_workable_items_cache';

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
		$output = \get_transient(self::CACHE_WORKABLE_ITEMS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

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

		$body = [
			'sourced' => false,
			'candidate' => \array_merge_recursive(
				$paramsPrepared,
				$paramsFiles
			),
		];

		$url = "{$this->getBaseUrl()}jobs/{$itemId}/candidates";

		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode($body),
			]
		);

		// Structure response details.
		$details = $this->getIntegrationApiReponseDetails(
			SettingsWorkable::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$paramsPrepared,
			$paramsFiles,
			$itemId,
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
		return $this->getIntegrationApiReponseDetails(
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
	private function getWorkableItems()
	{
		$url = "{$this->getBaseUrl()}jobs?limit=100";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		$details = $this->getIntegrationApiReponseDetails(
			SettingsWorkable::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details['code'];
		$body = $details['body'];

		// On success return output.
		if ($code >= 200 && $code <= 299) {
			return $body['jobs'] ?? [];
		}

		return [];
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
		$details = $this->getIntegrationApiReponseDetails(
			SettingsWorkable::SETTINGS_TYPE_KEY,
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
		// Map enrichment data.
		$params = $this->enrichment->mapEnrichmentFields($params);

		// Remove unecesery params.
		$params = Helper::removeUneceseryParamFields($params);

		$output = [];
		$answers = [];

		$filterName = Filters::getFilterName(['integrations', SettingsWorkable::SETTINGS_TYPE_KEY, 'prePostParams']);
		if (\has_filter($filterName)) {
			$params = \apply_filters($filterName, $params) ?? [];
		}

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
					$answers[] = [
						'question_key' => $name,
						'checked' => \filter_var($value, \FILTER_VALIDATE_BOOLEAN),
					];
					break;
				case 'multiple_choice':
				case 'dropdown':
					$answers[] = [
						'question_key' => $name,
						'choices' => \explode(',', $value),
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
				$fileName = $this->getFileNameFromPath($file);

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
	 * Return Subdomain from settings or global vairaible.
	 *
	 * @return string
	 */
	private function getSubdomain(): string
	{
		$subdomain = Variables::getSubdomainWorkable();

		return $subdomain ? $subdomain : $this->getOptionValue(SettingsWorkable::SETTINGS_WORKABLE_SUBDOMAIN_KEY);
	}

	/**
	 * Return Api Key from settings or global vairaible.
	 *
	 * @return string
	 */
	private function getApiKey(): string
	{
		$apiKey = Variables::getApiKeyWorkable();

		return $apiKey ? $apiKey : $this->getOptionValue(SettingsWorkable::SETTINGS_WORKABLE_API_KEY_KEY);
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
}
