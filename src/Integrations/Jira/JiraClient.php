<?php

/**
 * Jira Client integration class.
 *
 * @package EightshiftForms\Integrations\Jira
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Jira;

use EightshiftForms\Cache\SettingsCache;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsDeveloperHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;

/**
 * JiraClient integration class.
 */
class JiraClient implements JiraClientInterface
{
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
		if (UtilsDeveloperHelper::isDeveloperSkipCacheActive()) {
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
		if (UtilsDeveloperHelper::isDeveloperSkipCacheActive()) {
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
	 * API request to post application.
	 *
	 * @param array<string, mixed> $formDetails Form details.
	 *
	 * @return array<string, mixed>
	 */
	public function postApplication(array $formDetails): array
	{
		$params = $formDetails[UtilsConfig::FD_PARAMS];
		$files = $formDetails[UtilsConfig::FD_FILES];
		$formId = $formDetails[UtilsConfig::FD_FORM_ID];

		// Filter override post request.
		$filterName = UtilsHooksHelper::getFilterName(['integrations', SettingsJira::SETTINGS_TYPE_KEY, 'overridePostRequest']);
		if (\has_filter($filterName)) {
			$filterValue = \apply_filters($filterName, [], $params, $files, $formId) ?? [];

			if ($filterValue) {
				return $filterValue;
			}
		}

		$url = $this->getBaseUrl() . "issue";

		$body = [
			'fields' => $this->prepareParams($params, $files, $formId),
		];

		$response = \wp_remote_post(
			$url,
			[
				'headers' => $this->getHeaders(),
				'body' => \wp_json_encode($body),
			]
		);

		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsJira::SETTINGS_TYPE_KEY,
			$response,
			$url,
			$body,
			[],
			'',
			$formId,
			UtilsSettingsHelper::isOptionCheckboxChecked(SettingsJira::SETTINGS_JIRA_SKIP_INTEGRATION_KEY, SettingsJira::SETTINGS_JIRA_SKIP_INTEGRATION_KEY)
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

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
	 * Return base output url prefix.
	 *
	 * @return string
	 */
	public function getBaseUrlOutputPrefix(): string
	{
		$output = UtilsSettingsHelper::getOptionValue(SettingsJira::SETTINGS_JIRA_API_BOARD_URL_KEY);

		if (!$output) {
			$output = $this->getApiBoard();
		}

		$output = $this->cleanBoard($output);

		return "https://{$output}/";
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

		return UtilsApiHelper::getIntegrationApiReponseDetails(
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
		return (bool) UtilsSettingsHelper::getOptionValue(SettingsJira::SETTINGS_JIRA_SELF_HOSTED_KEY);
	}

	/**
	 * Return base url prefix.
	 *
	 * @return string
	 */
	private function getBaseUrlPrefix(): string
	{
		$output = $this->cleanBoard($this->getApiBoard());

		return "https://{$output}/";
	}

	/**
	 * Return base url claned from user input.
	 *
	 * @param string $output Output to clean.
	 *
	 * @return string
	 */
	private function cleanBoard(string $output): string
	{
		$output = \str_replace('https://', '', $output);
		$output = \str_replace('http://', '', $output);
		$output = \str_replace('www.', '', $output);
		$output = \rtrim($output, '/');
		$output = \ltrim($output, '/');

		return $output;
	}

	/**
	 * Get projects from the api.
	 *
	 * @return array<mixed>
	 */
	private function getJiraProjects()
	{
		$details = $this->getTestApi();

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
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

		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsJira::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
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

		$details = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsJira::SETTINGS_TYPE_KEY,
			$response,
			$url,
		);

		$code = $details[UtilsConfig::IARD_CODE];
		$body = $details[UtilsConfig::IARD_BODY];

		// On success return output.
		if ($code >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $code <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
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
	 * @param array<string, mixed> $files Files.
	 * @param string $formId FormId value.
	 *
	 * @return array<string, string>
	 */
	private function prepareParams(array $params, array $files, string $formId): array
	{
		$output = [];

		$selectedProject = UtilsSettingsHelper::getSettingValue(SettingsJira::SETTINGS_JIRA_PROJECT_KEY, $formId);

		if (!$selectedProject) {
			return $output;
		}

		$selectedIssueType = UtilsSettingsHelper::getSettingValue(SettingsJira::SETTINGS_JIRA_ISSUE_TYPE_KEY, $formId);
		if (!$selectedIssueType) {
			return $output;
		}

		$title = UtilsSettingsHelper::getSettingValue(SettingsJira::SETTINGS_JIRA_TITLE_KEY, $formId);
		if (!$title) {
			return $output;
		}

		// Remove unecesery params.
		$params = UtilsGeneralHelper::removeUneceseryParamFields($params);

		$params = \array_merge($params, $files);

		$formTitle = \get_the_title((int) $formId);

		$additionalDescription = UtilsSettingsHelper::getSettingValue(SettingsJira::SETTINGS_JIRA_DESC_KEY, $formId);

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
			if (!UtilsSettingsHelper::getSettingValue(SettingsJira::SETTINGS_JIRA_PARAMS_MANUAL_MAP_KEY, $formId)) {
				$contentOutput = [];

				$i = 0;
				foreach ($params as $param) {
					$value = $param['value'] ?? '';
					$name = $param['name'] ?? '';
					$type = $param['type'] ?? '';

					if (!$value || !$name || !$type) {
						continue;
					}

					if ($type === 'file') {
						$value = \array_map(
							static function (string $file) {
								$filename = \pathinfo($file, \PATHINFO_FILENAME);
								$extension = \pathinfo($file, \PATHINFO_EXTENSION);
								return "{$filename}.{$extension}";
							},
							$value
						);
					}

					if (\is_array($value)) {
						$value = \implode(', ', $value);
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
			$mapParams = UtilsSettingsHelper::getSettingValueGroup(SettingsJira::SETTINGS_JIRA_PARAMS_MAP_KEY, $formId);
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
			if (!UtilsSettingsHelper::getSettingValue(SettingsJira::SETTINGS_JIRA_PARAMS_MANUAL_MAP_KEY, $formId)) {
				$i = 0;
				foreach ($params as $param) {
					$value = $param['value'] ?? '';
					$name = $param['name'] ?? '';
					$type = $param['type'] ?? '';

					if (!$value || !$name || !$type) {
						continue;
					}

					if ($type === 'file') {
						$value = \array_map(
							static function (string $file) {
								$filename = \pathinfo($file, \PATHINFO_FILENAME);
								$extension = \pathinfo($file, \PATHINFO_EXTENSION);
								return "{$filename}.{$extension}";
							},
							$value
						);
					}

					if (\is_array($value)) {
						$value = \implode(', ', $value);
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
		return UtilsSettingsHelper::getOptionWithConstant(Variables::getApiKeyJira(), SettingsJira::SETTINGS_JIRA_API_KEY_KEY);
	}

	/**
	 * Return Api Board from settings or global variable.
	 *
	 * @return string
	 */
	private function getApiBoard(): string
	{
		return UtilsSettingsHelper::getOptionWithConstant(Variables::getApiBoardJira(), SettingsJira::SETTINGS_JIRA_API_BOARD_KEY);
	}

	/**
	 * Return Api User from settings or global variable.
	 *
	 * @return string
	 */
	private function getApiUser(): string
	{
		return UtilsSettingsHelper::getOptionWithConstant(Variables::getApiUserJira(), SettingsJira::SETTINGS_JIRA_API_USER_KEY);
	}
}
