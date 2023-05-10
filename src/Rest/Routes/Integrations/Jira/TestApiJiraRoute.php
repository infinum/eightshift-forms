<?php

/**
 * The class register route for public form submiting endpoint - Jira
 *
 * @package EightshiftForms\Rest\Route\Integrations\Jira
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Jira;

use EightshiftForms\Integrations\Jira\JiraClientInterface;
use EightshiftForms\Integrations\Jira\SettingsJira;
use EightshiftForms\Rest\Routes\AbstractTestApi;

/**
 * Class TestApiJiraRoute
 */
class TestApiJiraRoute extends AbstractTestApi
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/' . AbstractTestApi::ROUTE_PREFIX_TEST_API . '-' . SettingsJira::SETTINGS_TYPE_KEY . '/';

	/**
	 * Instance variable for Jira data.
	 *
	 * @var JiraClientInterface
	 */
	protected $jiraClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param JiraClientInterface $jiraClient Inject Jira which holds Jira connect data.
	 */
	public function __construct(JiraClientInterface $jiraClient)
	{
		$this->jiraClient = $jiraClient;
	}

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return self::ROUTE_SLUG;
	}

	/**
	 * Implement test action.
	 *
	 * @return mixed
	 */
	protected function testAction()
	{
		return $this->jiraClient->getTestApi();
	}
}
