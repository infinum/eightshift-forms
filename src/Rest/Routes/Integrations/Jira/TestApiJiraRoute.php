<?php

/**
 * The class register route for public form submitting endpoint - Jira
 *
 * @package EightshiftForms\Rest\Route\Integrations\Jira
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Jira;

use EightshiftForms\Config\Config;
use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Integrations\Jira\JiraClientInterface;
use EightshiftForms\Integrations\Jira\SettingsJira;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftLibs\Rest\Routes\AbstractRoute;

/**
 * Class TestApiJiraRoute
 */
class TestApiJiraRoute extends AbstractSimpleFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsJira::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable for Jira data.
	 *
	 * @var JiraClientInterface
	 */
	protected $jiraClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param SecurityInterface $security Inject security methods.
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param JiraClientInterface $jiraClient Inject Jira which holds Jira connect data.
	 */
	public function __construct(
		SecurityInterface $security,
		ValidatorInterface $validator,
		LabelsInterface $labels,
		JiraClientInterface $jiraClient
	) {
		$this->security = $security;
		$this->validator = $validator;
		$this->labels = $labels;
		$this->jiraClient = $jiraClient;
	}

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/' . Config::ROUTE_PREFIX_TEST_API . '/' . self::ROUTE_SLUG;
	}

	/**
	 * Get mandatory params.
	 *
	 * @param array<string, mixed> $params Params passed from the request.
	 *
	 * @return array<string, string>
	 */
	protected function getMandatoryParams(array $params): array
	{
		return [
			'type' => 'string',
		];
	}

	/**
	 * Check if the route is admin protected.
	 *
	 * @return boolean
	 */
	protected function isRouteAdminProtected(): bool
	{
		return true;
	}

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $params Prepared params.
	 *
	 * @throws BadRequestException If Jira is not configured.
	 *
	 * @return array<string, mixed>
	 */
	protected function submitAction(array $params): array
	{
		$output = $this->jiraClient->getTestApi();

		if ($output[Config::IARD_STATUS] === AbstractRoute::STATUS_ERROR) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				$this->getLabels()->getLabel('testApiError'),
				[
					AbstractBaseRoute::R_DEBUG => $output,
					AbstractBaseRoute::R_DEBUG_KEY => 'testApiError',
				]
			);
			// phpcs:enable
		}

		return [
			AbstractBaseRoute::R_MSG => $this->getLabels()->getLabel('testApiSuccess'),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG => $output,
				AbstractBaseRoute::R_DEBUG_KEY => 'testApiSuccess',
			],
		];
	}
}
