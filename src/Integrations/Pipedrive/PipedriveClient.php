<?php

/**
 * Pipedrive Client integration class.
 *
 * @package EightshiftForms\Integrations\Pipedrive
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Pipedrive;

use EightshiftForms\Cache\SettingsCache;
use EightshiftForms\Enrichment\EnrichmentInterface;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Rest\ApiHelper;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftForms\Troubleshooting\SettingsDebug;
use EightshiftForms\Validation\Validator;
use EightshiftFormsVendor\EightshiftLibs\Helpers\ObjectHelperTrait;

/**
 * PipedriveClient integration class.
 */
class PipedriveClient implements PipedriveClientInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Helper trait.
	 */
	use ObjectHelperTrait;

	/**
	 * Use API helper trait.
	 */
	use ApiHelper;

	/**
	 * Return Pipedrive base url.
	 *
	 * @var string
	 */
	private const BASE_URL = 'https://api.pipedrive.com/v1/';

	/**
	 * Transient cache name for projects.
	 */
	public const CACHE_PIPEDRIVE_PROJECTS_TRANSIENT_NAME = 'es_pipedrive_projects_cache';

	/**
	 * Transient cache name for issue types.
	 */
	public const CACHE_PIPEDRIVE_ISSUE_TYPE_TRANSIENT_NAME = 'es_pipedrive_issue_type_cache';

	/**
	 * Issue type epic.
	 */
	public const ISSUE_TYPE_EPIC = '10000';

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
	 * Return projects.
	 *
	 * @param bool $hideUpdateTime Determin if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getProjects(bool $hideUpdateTime = true): array
	{

		$output = \get_transient(self::CACHE_PIPEDRIVE_PROJECTS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Prevent cache.
		if ($this->isOptionCheckboxChecked(SettingsDebug::SETTINGS_DEBUG_SKIP_CACHE_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY)) {
			$output = [];
		}

		// Check if form exists in cache.
		if (!$output) {
			$items = $this->getPipedriveProjects();

			if ($items) {
				$fields = $this->getPipedriveCustomFields();

				foreach ($items as $item) {
					$id = $item['id'] ?? '';

					$output[$id] = [
						'id' => $id,
						'key' => $item['key'] ?? '',
						'title' => $item['name'] ?? '',
						'issueTypes' => [],
						'customFields' => $fields,
					];
				}

				$output[ClientInterface::TRANSIENT_STORED_TIME] = [
					'id' => ClientInterface::TRANSIENT_STORED_TIME,
					'title' => \current_datetime()->format('Y-m-d H:i:s'),
				];

				\set_transient(self::CACHE_PIPEDRIVE_PROJECTS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
			}
		}

		if ($hideUpdateTime) {
			unset($output[ClientInterface::TRANSIENT_STORED_TIME]);
		}

		return $output;
	}

	/**
	 * Return projects issue types.
	 *
	 * @param string $itemId Item ID to search by.
	 *
	 * @return array<string, mixed>
	 */
	public function getIssueType(string $itemId): array
	{
		$output = \get_transient(self::CACHE_PIPEDRIVE_PROJECTS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Prevent cache.
		if ($this->isOptionCheckboxChecked(SettingsDebug::SETTINGS_DEBUG_SKIP_CACHE_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY)) {
			$output = [];
		}

		$projectId = $this->getProjectIdByKey($itemId);

		// Check if form exists in cache.
		if (!$output || !$projectId || !isset($output[$itemId]) || !$output[$itemId] || !$output[$itemId]['issueTypes']) {
			$items = $this->getPipedriveIssueTypes($projectId);

			if ($items) {
				foreach ($items as $item) {
					if (isset($item['hierarchyLevel']) && $item['hierarchyLevel'] === -1) {
						continue;
					}

					$id = $item['id'] ?? '';

					$output[$projectId]['issueTypes'][$id] = [
						'id' => $id,
						'title' => $item['name'] ?? '',
					];
				}

				\set_transient(self::CACHE_PIPEDRIVE_PROJECTS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
			}
		}

		return $output[$projectId]['issueTypes'] ?? [];
	}

	/**
	 * API request to post issue.
	 *
	 * @param array<string, mixed> $params Params array.
	 * @param string $formId FormId value.
	 *
	 * @return array<string, mixed>
	 */
	public function postIssue(array $params, string $formId): array
	{
		$url = self::BASE_URL . "persons";

		$body = [
			// 'fields' => $this->prepareParams($params, $formId),
		];

		$response = \wp_remote_post(
			$this->getApiUrl($url),
			[
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode($body),
			]
		);

		error_log( print_r( ( $response ), true ) );

		$details = $this->getIntegrationApiReponseDetails(
			SettingsPipedrive::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$body,
			[],
			'',
			$formId,
			$this->isOptionCheckboxChecked(SettingsPipedrive::SETTINGS_PIPEDRIVE_SKIP_INTEGRATION_KEY, SettingsPipedrive::SETTINGS_PIPEDRIVE_SKIP_INTEGRATION_KEY)
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
	 * Get projects custom fields list.
	 *
	 * @param string $projectId Project Id to get fields from.
	 *
	 * @return array<string, mixed>
	 */
	public function getProjectsCustomFields(string $projectId): array
	{
		$ignoreKeys = [
			'project' => 0,
			'issuetype' => 1,
			'description' => 2,
			'parent' => 2,
		];

		$projectId = $this->getProjectIdByKey($projectId);

		return \array_map(
			static function ($item) use ($ignoreKeys) {
				if (!isset($ignoreKeys[$item['id']])) {
					return $item;
				}
			},
			$this->getProjects()[$projectId]['customFields'] ?? [],
		);
	}

	/**
	 * Get test api.
	 *
	 * @return array<mixed>
	 */
	public function getTestApi(): array
	{
		$url = self::BASE_URL . "activities";

		$response = \wp_remote_get(
			$this->getApiUrl($url),
			[
				'headers' => $this->getHeaders(),
			]
		);

		return $this->getIntegrationApiReponseDetails(
			SettingsPipedrive::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);
	}

	/**
	 * Get projects from the api.
	 *
	 * @return array<mixed>
	 */
	private function getPipedriveProjects()
	{
		$details = $this->getTestApi();

		$code = $details['code'];
		$body = $details['body'];

		// On success return output.
		if ($code >= 200 && $code <= 299) {
			return $body['values'] ?? [];
		}

		return [];
	}

	/**
	 * Get issue types from the api.
	 *
	 * @param string $itemId Item ID to search by.
	 *
	 * @return array<mixed>
	 */
	private function getPipedriveIssueTypes(string $itemId)
	{

		$url = self::BASE_URL . "issuetype/project?projectId={$itemId}";

		$response = \wp_remote_get(
			$this->getApiUrl($url),
			[
				'headers' => $this->getHeaders(),
			]
		);

		$details = $this->getIntegrationApiReponseDetails(
			SettingsPipedrive::SETTINGS_TYPE_KEY,
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
	 * Get custom fields from the api.
	 *
	 * @return array<mixed>
	 */
	private function getPipedriveCustomFields()
	{

		$url = self::BASE_URL . "field";

		$response = \wp_remote_get(
			$this->getApiUrl($url),
			[
				'headers' => $this->getHeaders(),
			]
		);

		$details = $this->getIntegrationApiReponseDetails(
			SettingsPipedrive::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details['code'];
		$body = $details['body'];

		// On success return output.
		if ($code >= 200 && $code <= 299) {
			return \array_map(
				static function ($item) {
					return [
						'id' => $item['id'],
						'title' => $item['name'],
					];
				},
				$body
			);
		}

		return [];
	}

	/**
	 * Output project Id by project key.
	 *
	 * @param string $projectId Project Id from API.
	 *
	 * @return string
	 */
	private function getProjectIdByKey(string $projectId): string
	{
		return \array_values(\array_filter(
			$this->getProjects(),
			static function ($item) use ($projectId) {
				return $item['key'] === $projectId;
			}
		))[0]['id'] ?? '';
	}

	/**
	 * Prepare params
	 *
	 * @param array<string, mixed> $params Params.
	 * @param string $formId FormId value.
	 *
	 * @return array<string, string>
	 */
	private function prepareParams(array $params, string $formId): array
	{
		$output = [];

		$selectedProject = $this->getSettingValue(SettingsPipedrive::SETTINGS_PIPEDRIVE_PROJECT_KEY, $formId);

		if (!$selectedProject) {
			return $output;
		}

		$selectedIssueType = $this->getSettingValue(SettingsPipedrive::SETTINGS_PIPEDRIVE_ISSUE_TYPE_KEY, $formId);
		if (!$selectedIssueType) {
			return $output;
		}

		$title = $this->getSettingValue(SettingsPipedrive::SETTINGS_PIPEDRIVE_TITLE_KEY, $formId);
		if (!$title) {
			return $output;
		}

		// Map enrichment data.
		$params = $this->enrichment->mapEnrichmentFields($params);

		// Remove unecesery params.
		$params = Helper::removeUneceseryParamFields($params);

		$formTitle = \get_the_title((int) $formId);

		$additionalDescription = $this->getSettingValue(SettingsPipedrive::SETTINGS_PIPEDRIVE_DESC_KEY, $formId);

		$output = [
			'project' => [
				'key' => $selectedProject,
			],
			'issuetype' => [
				'id' => $selectedIssueType,
			],
			'summary' => $title,
		];

		// Add header.
		// translators: %1$s will be replaced with form name, and %2$s with new line break.
		$descriptionOutput = \sprintf(\__('Data populated from the WordPress "%1$s" form: %2$s %2$s', 'eightshift-forms'), \esc_html($formTitle), \PHP_EOL);

		// Standard fields output.
		if (!$this->getSettingValue(SettingsPipedrive::SETTINGS_PIPEDRIVE_PARAMS_MANUAL_MAP_KEY, $formId)) {
			$i = 0;
			foreach ($params as $param) {
				$value = $param['value'] ?? '';
				if (!$value) {
					continue;
				}

				$name = $param['name'] ?? '';
				if (!$name) {
					continue;
				}

				$descriptionOutput .= \esc_html($name) . ':' . \PHP_EOL . \esc_html($value) . \PHP_EOL . \PHP_EOL;

				$i++;
			}

			// Additional desc.
			if ($additionalDescription) {
				$descriptionOutput .= \PHP_EOL . \PHP_EOL . \esc_html($additionalDescription);
			}

			// Custom fields maps is not suported.
		}

		// Populate output desc.
		$output['description'] = $descriptionOutput;

		return $output;
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
		return [];
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
		$msg = $body['errors'] ?? [];

		if (isset($body['errors']['project'])) {
			return 'pipedriveMissingProject';
		}

		if (isset($body['errors']['issuetype'])) {
			return 'pipedriveMissingIssueType';
		}

		if (isset($body['errors']['summary'])) {
			return 'pipedriveMissingSummary';
		}

		if (isset($body['errors']['customfield_10011'])) {
			return 'pipedriveMissingEpicName';
		}

		switch ($msg) {
			case 'auth_required':
				return 'pipedriveAuthRequired';
			case 'email_invalid':
				return 'pipedriveInvalidEmail';
			default:
				return 'submitWpError';
		}
	}

	/**
	 * Get api token.
	 *
	 * @param string $url Url to get token from.
	 *
	 * @return string
	 */
	private function getApiUrl(string $url): string
	{
		$url = rtrim($url, '/');

		return $url . '/?api_token=' . $this->getApiKey();
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
	 * Return Api Key from settings or global variable.
	 *
	 * @return string
	 */
	private function getApiKey(): string
	{
		$apiKey = Variables::getApiKeyPipedrive();

		return $apiKey ? $apiKey : $this->getOptionValue(SettingsPipedrive::SETTINGS_PIPEDRIVE_API_KEY_KEY);
	}
}
