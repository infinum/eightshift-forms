<?php

/**
 * The class to provide forms locations usage.
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\CustomPostType\Result;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
use WP_REST_Request;

/**
 * Class LocationsRoute
 */
class LocationsRoute extends AbstractSimpleFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'locations';

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
	 * @param array<string, mixed> $formDetails Data passed from the `getFormDetailsApi` function.
	 *
	 * @return array<string, string>
	 */
	protected function getMandatoryParams(array $params): array
	{
		return [
			'id' => 'string',
			'type' => 'string',
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
		$id = $params['id'] ?? '';
		$usageType = $params['type'] ?? '';

		switch ($usageType) {
			case Result::POST_TYPE_SLUG:
				$errorMsg = $this->getLabels()->getLabel('locationsResultOutputError');
				break;
			default:
				$errorMsg = $this->getLabels()->getLabel('locationsFormError');
				break;
		}

		$type = GeneralHelpers::getFormTypeById($id);

		return [
			AbstractBaseRoute::R_MSG => $this->getLabels()->getLabel('locationsSuccess'),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG_KEY => 'locationsSuccess',
			],
			AbstractBaseRoute::R_DATA => [
				UtilsHelper::getStateResponseOutputKey('adminLocations') => Helpers::render(
					'item-details',
					[
						'items' => GeneralHelpers::getBlockLocations($id, $usageType),
						'type' => $type,
						'sectionClass' => Helpers::getComponent('admin-listing')['componentClass'],
						'emptyContent' => $errorMsg,
						'additionalAttributes' => [
							UtilsHelper::getStateAttribute('adminIntegrationType') => $type,
						],
					],
					'components',
					false,
					'admin-listing/partials'
				),
			],
		];
	}
}
