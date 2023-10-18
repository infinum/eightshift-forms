<?php

/**
 * File containing Jira specific interface.
 *
 * @package EightshiftForms\Integrations\Jira
 */

namespace EightshiftForms\Integrations\Jira;

use EightshiftForms\Integrations\ClientMappingInterface;

/**
 * Interface for a Client
 */
interface JiraClientInterface extends ClientMappingInterface
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
	 * Use self-hosted or cloud version.
	 *
	 * @return bool
	 */
	public function isSelfHosted(): bool;
}
