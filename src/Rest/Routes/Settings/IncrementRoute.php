<?php

/**
 * The class register route for increment endpoint.
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;

/**
 * Class IncrementRoute
 */
class IncrementRoute extends AbstractSimpleFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'increment';

	/**
	 * Get the base url of the route.
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
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, string>
	 */
	protected function getMandatoryParams(array $params): array
	{
		return [
			'formId' => 'string',
		];
	}

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $params Prepared params.
	 *
	 * @return array<string, mixed>
	 */
	protected function submitAction(array $params): array
	{
		FormsHelper::resetIncrement($params['formId'] ?? '');

		return [
			AbstractBaseRoute::R_MSG => $this->getLabels()->getLabel('incrementResetSuccess'),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG_KEY => 'incrementResetSuccess',
			],
		];
	}
}
