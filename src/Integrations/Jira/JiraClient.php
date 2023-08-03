<?php

/**
 * Jira Client integration class.
 *
 * @package EightshiftForms\Integrations\Jira
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Jira;

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
 * JiraClient integration class.
 */
class JiraClient implements JiraClientInterface
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
	 * Transient cache name for projects.
	 */
	public const CACHE_JIRA_PROJECTS_TRANSIENT_NAME = 'es_jira_projects_cache';

	/**
	 * Transient cache name for issue types.
	 */
	public const CACHE_JIRA_ISSUE_TYPE_TRANSIENT_NAME = 'es_jira_issue_type_cache';

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

		$output = \get_transient(self::CACHE_JIRA_PROJECTS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Prevent cache.
		if ($this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_SKIP_CACHE_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY)) {
			$output = [];
		}

		// Check if form exists in cache.
		if (!$output) {
			$items = $this->getJiraProjects();

			if ($items) {
				$fields = $this->getJiraCustomFields();

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

				\set_transient(self::CACHE_JIRA_PROJECTS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
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
		$output = \get_transient(self::CACHE_JIRA_PROJECTS_TRANSIENT_NAME) ?: []; // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found

		// Prevent cache.
		if ($this->isCheckboxOptionChecked(SettingsDebug::SETTINGS_DEBUG_SKIP_CACHE_KEY, SettingsDebug::SETTINGS_DEBUG_DEBUGGING_KEY)) {
			$output = [];
		}

		$projectId = $this->getProjectIdByKey($itemId);

		// Check if form exists in cache.
		if (!$output || !$projectId || !isset($output[$itemId]) || !$output[$itemId] || !$output[$itemId]['issueTypes']) {
			$items = $this->getJiraIssueTypes($projectId);

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

				\set_transient(self::CACHE_JIRA_PROJECTS_TRANSIENT_NAME, $output, SettingsCache::CACHE_TRANSIENTS_TIMES['integration']);
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
		$url = $this->getBaseUrl() . "issue";

		$body = [
			'fields' => $this->prepareParams($params, $formId),
		];

		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode($body),
			]
		);

		$details = $this->getIntegrationApiReponseDetails(
			SettingsJira::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$body,
			[],
			'',
			$formId,
			$this->isCheckboxOptionChecked(SettingsJira::SETTINGS_JIRA_SKIP_INTEGRATION_KEY, SettingsJira::SETTINGS_JIRA_SKIP_INTEGRATION_KEY)
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
	 * Return base url prefix.
	 *
	 * @return string
	 */
	public function getBaseUrlPrefix(): string
	{
		$board = $this->getApiBoard();

		return "https://{$board}/";
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
		$url = $this->getBaseUrl() . "project/search";

		if ($this->isSelfHosted()) {
			$url = $this->getBaseUrl() . "project";
		}

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		return $this->getIntegrationApiReponseDetails(
			SettingsJira::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);
	}

	/**
	 * Use self-hosted or cloud version.
	 *
	 * @return bool
	 */
	public function isSelfHosted(): bool
	{
		return (bool) $this->getOptionValue(SettingsJira::SETTINGS_JIRA_SELF_HOSTED_KEY);
	}

	/**
	 * Get projects from the api.
	 *
	 * @return array<mixed>
	 */
	private function getJiraProjects()
	{
		$details = $this->getTestApi();

		$code = $details['code'];
		$body = $details['body'];

		// On success return output.
		if ($code >= 200 && $code <= 299) {
			if ($this->isSelfHosted()) {
				return $body ?? [];
			} else {
				return $body['values'] ?? [];
			}
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
	private function getJiraIssueTypes(string $itemId)
	{

		$url = $this->getBaseUrl() . "issuetype/project?projectId={$itemId}";

		if ($this->isSelfHosted()) {
			$url = $this->getBaseUrl() . "project/{$itemId}";
		}

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		$details = $this->getIntegrationApiReponseDetails(
			SettingsJira::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details['code'];
		$body = $details['body'];

		// On success return output.
		if ($code >= 200 && $code <= 299) {
			if ($this->isSelfHosted()) {
				return $body['issueTypes'] ?? [];
			} else {
				return $body ?? [];
			}
		}

		return [];
	}

	/**
	 * Get custom fields from the api.
	 *
	 * @return array<mixed>
	 */
	private function getJiraCustomFields()
	{

		$url = $this->getBaseUrl() . "field";

		if ($this->isSelfHosted()) {
			$url = $this->getBaseUrl() . "customFields";
		}

		$response = \wp_remote_get(
			$url,
			[
				'headers' => $this->getHeaders(),
			]
		);

		$details = $this->getIntegrationApiReponseDetails(
			SettingsJira::SETTINGS_TYPE_KEY,
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

		$selectedProject = $this->getSettingsValue(SettingsJira::SETTINGS_JIRA_PROJECT_KEY, $formId);

		if (!$selectedProject) {
			return $output;
		}

		$selectedIssueType = $this->getSettingsValue(SettingsJira::SETTINGS_JIRA_ISSUE_TYPE_KEY, $formId);
		if (!$selectedIssueType) {
			return $output;
		}

		$title = $this->getSettingsValue(SettingsJira::SETTINGS_JIRA_TITLE_KEY, $formId);
		if (!$title) {
			return $output;
		}

		// Map enrichment data.
		$params = $this->enrichment->mapEnrichmentFields($params);

		// Remove unecesery params.
		$params = Helper::removeUneceseryParamFields($params);

		$formTitle = \get_the_title((int) $formId);

		$additionalDescription = $this->getSettingsValue(SettingsJira::SETTINGS_JIRA_DESC_KEY, $formId);

		$output = [
			'project' => [
				'key' => $selectedProject,
			],
			'issuetype' => [
				'id' => $selectedIssueType,
			],
			'summary' => $title,
		];

		if (!$this->isSelfHosted()) {
			$output['description'] = [
				'type' => 'doc',
				'version' => 1,
				'content' => [],
			];

			// Standard fields output.
			if (!$this->getSettingsValue(SettingsJira::SETTINGS_JIRA_PARAMS_MANUAL_MAP_KEY, $formId)) {
				$contentOutput = [];

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

					$contentOutput[] = [
						'type' => 'tableRow',
						'content' => [
							[
								'type' => 'tableCell',
								'content' => [
									[
										'type' => 'paragraph',
										'content' => [
											[
												'type' => 'text',
												'text' => \esc_html($name),
											],
										],
									],
								],
							],
							[
								'type' => 'tableCell',
								'content' => [
									[
										'type' => 'paragraph',
										'content' => [
											[
												'type' => 'text',
												'text' => \esc_html($value),
											],
										],
									],
								],
							],
						],
					];

					$i++;
				}

				$output['description']['content'] = [
					[
						'type' => 'paragraph',
						'content' => [
							[
								'type' => 'text',
								// translators: %s will be replaced with the form title name.
								'text' => \sprintf(\__('Data populated from the WordPress "%1$s" form:', 'eightshift-forms'), \esc_html($formTitle)),
							],
						],
					],
					[
						'type' => 'table',
						'attrs' => [
							'isNumberColumnEnabled' => false,
							'layout' => 'default',
						],
						'content' => \array_merge(
							[
								[
									'type' => 'tableRow',
									'content' => [
										[
											'type' => 'tableCell',
											'attrs' => [
												'background' => 'lavender',
											],
											'content' => [
												[
													'type' => 'paragraph',
													'content' => [
														[
															'type' => 'text',
															'text' => \__('Field Name', 'eightshift-forms'),
														],
													],
												],
											],
										],
										[
											'type' => 'tableCell',
											'attrs' => [
												'background' => 'lavender',
											],
											'content' => [
												[
													'type' => 'paragraph',
													'content' => [
														[
															'type' => 'text',
															'text' => \__('Field Value', 'eightshift-forms'),
														],
													],
												],
											],
										],
									],
								],
							],
							$contentOutput
						),
					],
				];
			}

			// Additional desc.
			if ($additionalDescription) {
				\array_unshift($output['description']['content'], [
					'type' => 'paragraph',
					'content' => [
						[
							'type' => 'text',
							'text' => \esc_html($additionalDescription),
						],
					],
				]);
			}

			// Custom fields maps output.
			$mapParams = $this->getSettingsValueGroup(SettingsJira::SETTINGS_JIRA_PARAMS_MAP_KEY, $formId);
			if ($mapParams) {
				foreach ($mapParams as $key => $value) {
					if (!$value) {
						continue;
					}

					$output[$key] = $value;
				}
			}
		} else {
			// Add header.
			// translators: %1$s will be replaced with form name, and %2$s with new line break.
			$descriptionOutput = \sprintf(\__('Data populated from the WordPress "%1$s" form: %2$s %2$s', 'eightshift-forms'), \esc_html($formTitle), \PHP_EOL);

			// Standard fields output.
			if (!$this->getSettingsValue(SettingsJira::SETTINGS_JIRA_PARAMS_MANUAL_MAP_KEY, $formId)) {
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
		}

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
			return 'jiraMissingProject';
		}

		if (isset($body['errors']['issuetype'])) {
			return 'jiraMissingIssueType';
		}

		if (isset($body['errors']['summary'])) {
			return 'jiraMissingSummary';
		}

		if (isset($body['errors']['customfield_10011'])) {
			return 'jiraMissingEpicName';
		}

		switch ($msg) {
			case 'auth_required':
				return 'jiraAuthRequired';
			case 'email_invalid':
				return 'jiraInvalidEmail';
			default:
				return 'submitWpError';
		}
	}

	/**
	 * Set headers used for fetching data.
	 *
	 * @return array<string, mixed>
	 */
	private function getHeaders(): array
	{
		$key = $this->getApiKey();
		$user = $this->getApiUser();

		if ($this->isSelfHosted()) {
			$auth = "Bearer {$key}";
		} else {
			$auth = 'Basic ' . \base64_encode("{$user}:{$key}"); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		}

		return [
			'Content-Type' => 'application/json; charset=utf-8',
			'Authorization' => $auth,
		];
	}

	/**
	 * Return base url.
	 *
	 * @return string
	 */
	private function getBaseUrl(): string
	{
		$prefix = $this->getBaseUrlPrefix();

		$apiVersion = '3';

		if ($this->isSelfHosted()) {
			$apiVersion = '2';
		}

		return "{$prefix}rest/api/{$apiVersion}/";
	}

	/**
	 * Return Api Key from settings or global variable.
	 *
	 * @return string
	 */
	private function getApiKey(): string
	{
		$apiKey = Variables::getApiKeyJira();

		return $apiKey ? $apiKey : $this->getOptionValue(SettingsJira::SETTINGS_JIRA_API_KEY_KEY);
	}

	/**
	 * Return Api Board from settings or global variable.
	 *
	 * @return string
	 */
	private function getApiBoard(): string
	{
		$apiBoard = Variables::getApiBoardJira();

		return $apiBoard ? $apiBoard : $this->getOptionValue(SettingsJira::SETTINGS_JIRA_API_BOARD_KEY);
	}

	/**
	 * Return Api User from settings or global variable.
	 *
	 * @return string
	 */
	private function getApiUser(): string
	{
		$apiUser = Variables::getApiUserJira();

		return $apiUser ? $apiUser : $this->getOptionValue(SettingsJira::SETTINGS_JIRA_API_USER_KEY);
	}
}
