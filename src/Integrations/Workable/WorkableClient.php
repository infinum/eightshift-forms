<?php

/**
 * Workable Client integration class.
 *
 * @package EightshiftForms\Integrations\Workable
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Workable;

use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Rest\ApiHelper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

/**
 * WorkableClient integration class.
 */
class WorkableClient implements ClientInterface
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
	public const CACHE_WORKABLE_ITEMS_TRANSIENT_NAME = 'es_workable_items_cache';

	/**
	 * Transient cache name for item.
	 */
	public const CACHE_WORKABLE_ITEM_TRANSIENT_NAME = 'es_workable_item_cache';

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
		if (empty($output)) {
			$jobs = $this->getWorkableJobs();

			if ($jobs) {
				foreach ($jobs as $job) {
					$jobId = $job['shortcode'] ?? '';

					if (!$jobId) {
						continue;
					}

					$output[$jobId] = [
						'id' => (string) $jobId,
						'title' => $job['title'] ?? '',
					];
				}

				$output[ClientInterface::TRANSIENT_STORED_TIME] = [
					'id' => ClientInterface::TRANSIENT_STORED_TIME,
					'title' => \current_datetime()->format('Y-m-d H:i:s'),
				];

				\set_transient(self::CACHE_WORKABLE_ITEMS_TRANSIENT_NAME, $output, 3600);
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
		$output = \get_transient(self::CACHE_WORKABLE_ITEM_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Check if form exists in cache.
		if (empty($output) || !isset($output[$itemId]) || empty($output[$itemId])) {
			$job = $this->getWorkableJob($itemId);

			$formFields = $job['form_fields'] ?? [];
			$questions = $job['questions'] ?? [];

			if ($itemId && $formFields && $questions) {
				$output[$itemId] = \array_merge($formFields, $questions);

				\set_transient(self::CACHE_WORKABLE_ITEM_TRANSIENT_NAME, $output, 3600);
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
		$details = $this->getApiReponseDetails(
			SettingsWorkable::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$body,
			$files,
			$itemId,
			$formId
		);

		$code = $details['code'];
		$body = $details['body'];

		// On success return output.
		if ($code >= 200 && $code <= 299) {
			return $this->getApiSuccessOutput($details);
		}

		// Output error.
		return $this->getApiErrorOutput(
			$details,
			$this->getErrorMsg($body)
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
			case 'either name or firstname and lastname should be part of the candidate\'s payload':
				return 'workableInvalidRequiredError';
			case 'Validation failed: Name can\'t be blank, Firstname can\'t be blank':
				return 'workableInvalidFirstNameError';
			case 'Validation failed: Name can\'t be blank, Lastname can\'t be blank':
				return 'workableInvalidLastNameError';
			case 'Validation failed: Headline is too long (maximum is 255 characters)':
				return 'workableInvaldHeadlineError';
			case 'Validation failed: Custom attribute values body is too long (maximum is 128 characters)':
				return 'workableInvaldLinkError';
			default:
				return 'submitWpError';
		}
	}

	/**
	 * API request to get all jobs from Workable.
	 *
	 * @return array<string, mixed>
	 */
	private function getWorkableJobs()
	{
		$url = "{$this->getBaseUrl()}jobs?limit=100&state=closed,published";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		$details = $this->getApiReponseDetails(
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
	private function getWorkableJob(string $jobId)
	{
		$url = "{$this->getBaseUrl()}jobs/{$jobId}/application_form";

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		// Structure response details.
		$details = $this->getApiReponseDetails(
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
		$output = [];
		$answers = [];

		$customFields = \array_flip(Components::flattenArray(AbstractBaseRoute::CUSTOM_FORM_PARAMS));

		foreach ($params as $key => $param) {
			$value = $param['value'] ?? '';
			$internalType = $param['internalType'] ?? '';

			// Skip empty check if bool.
			if ($internalType !== 'boolean') {
				if (!$value) {
					continue;
				}
			}

			if ($key === 'es-form-storage') {
				$domain = '';
				if (isset($param['value']['utm_source'])) {
					$domain .= \ucfirst($param['value']['utm_source']);
				}
				if (isset($param['value']['utm_medium'])) {
					$domain .= '(' . \ucfirst($param['value']['utm_medium']) . ')';
				}

				if ($domain) {
					$output['domain'] = $domain;
				}
			}

			// Remove unecesery fields.
			if (isset($customFields[$key])) {
				continue;
			}

			$internalType = $param['internalType'] ?? '';

			switch ($internalType) {
				case 'free_text':
				case 'short_text':
					if ($key === 'summary' || $key === 'cover_letter') {
						$output[$key] = $value;
						break;
					}

					$answers[] = [
						'question_key' => $key,
						'body' => $value,
					];
					break;
				case 'boolean':
					$answers[] = [
						'question_key' => $key,
						'checked' => \filter_var($value, \FILTER_VALIDATE_BOOLEAN),
					];
					break;
				case 'multiple_choice':
				case 'dropdown':
					$answers[] = [
						'question_key' => $key,
						'choices' => \explode(',', $value),
					];
					break;
				default:
					$output[$key] = $value;
					break;
			}
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

		foreach ($files as $key => $items) {
			if (!$items) {
				continue;
			}

			foreach ($items as $file) {
				$fileName = $file['fileName'] ?? '';
				$path = $file['path'] ?? '';
				$id = $file['id'] ?? '';

				if (!$path) {
					continue;
				}

				if ($key === 'resume') {
					$output[$id] = [
						'name' => $fileName,
						'data' => \base64_encode(\file_get_contents($path)), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
					];
				} else {
					$answers[] = [
						'question_key' => $key,
						'file' => [
							'name' => $fileName,
							'data' => \base64_encode(\file_get_contents($path)), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
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
	 * Return Board Token from settings or global vairaible.
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

		return $apiKey ? $apiKey : $this->getOptionValue(SettingsWorkable::SETTINGS_WORKABLE_API_KEY_KEY); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
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
