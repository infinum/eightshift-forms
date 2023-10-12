<?php

/**
 * File containing Jira specific interface.
 *
 * @package EightshiftForms\Integrations\Jira
 */

namespace EightshiftForms\Integrations\Jira;

/**
 * Interface for a Client
 */
interface JiraClientInterface
{
	/**
	 * Return projects.
	 *
	 * @param bool $hideUpdateTime Determin if update time will be in the output or not.
	 *
	 * @return array<string, mixed>
	 */
	public function getProjects(bool $hideUpdateTime = true): array;

	/**
	 * Return projects issue types.
	 *
	 * @param string $itemId Item ID to search by.
	 *
	 * @return array<string, mixed>
	 */
	public function getIssueType(string $itemId): array;

	/**
	 * API request to post issue.
	 *
	 * @param array<string, mixed> $params Params array.
	 * @param string $formId FormId value.
	 *
	 * @return array<string, mixed>
	 */
	public function postIssue(array $params, string $formId): array;

	/**
	 * Return base output url prefix.
	 *
	 * @return string
	 */
	public function getBaseUrlOutputPrefix(): string;

	/**
	 * Get projects custom fields list.
	 *
	 * @param string $projectId Project Id to get fields from.
	 *
	 * @return array<string, mixed>
	 */
	public function getProjectsCustomFields(string $projectId): array;

	/**
	 * Get test api.
	 *
	 * @return array<mixed>
	 */
	public function getTestApi(): array;

	/**
	 * Use self-hosted or cloud version.
	 *
	 * @return bool
	 */
	public function isSelfHosted(): bool;
}
