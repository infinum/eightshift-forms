<?php

/**
 * The class register route for running cron jobs endpoint
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\CronJobs\SettingsCronJobs;
use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;

/**
 * Class CronRunRoute
 */
class CronRunRoute extends AbstractSimpleFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'cron-run';

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
	 * Check if the route is admin protected.
	 *
	 * @return boolean
	 */
	protected function isRouteAdminProtected(): bool
	{
		return true;
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
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $params Prepared params.
	 *
	 * @throws BadRequestException If cache type is not found.
	 *
	 * @return array<string, mixed>
	 */
	protected function submitAction(array $params): array
	{
		$type = $params['type'] ?? '';

		if (!\in_array($type, SettingsCronJobs::JOBS, true)) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				$this->labels->getLabel('cronRunNotFound')
			);
		}

		\do_action($type, [$this, 'getJobCallback']);

		// Finish.
		return [
			// translators: %1$s will be replaced with the cache type. %2$s will be replaced with the cache deleted success text.
			AbstractBaseRoute::R_MSG => $this->getLabels()->getLabel('cronRunSuccess'),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG_KEY => 'cronRunSuccess',
			],
		];
	}
}
