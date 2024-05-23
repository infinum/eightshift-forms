<?php

/**
 * The class to provide forms locations usage.
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Rest\Routes\AbstractUtilsBaseRoute;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
use WP_REST_Request;

/**
 * Class LocationsRoute
 */
class LocationsRoute extends AbstractUtilsBaseRoute
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
	 * Method that returns rest response
	 *
	 * @param WP_REST_Request $request Data got from endpoint url.
	 *
	 * @return WP_REST_Response|mixed If response generated an error, WP_Error, if response
	 *                                is already an instance, WP_HTTP_Response, otherwise
	 *                                returns a new WP_REST_Response instance.
	 */
	public function routeCallback(WP_REST_Request $request)
	{
		$premission = $this->checkUserPermission();
		if ($premission) {
			return \rest_ensure_response($premission);
		}

		$debug = [
			'request' => $request,
		];

		$params = $this->prepareSimpleApiParams($request, $this->getMethods());

		$id = $params['id'] ?? '';

		$type = UtilsGeneralHelper::getFormTypeById($id);

		return \rest_ensure_response(
			UtilsApiHelper::getApiSuccessPublicOutput(
				\esc_html__('Success', 'eightshift-forms'),
				[
					'output' => Helpers::render(
						'item-details',
						[
							'items' => UtilsGeneralHelper::getBlockLocations($id),
							'type' => UtilsGeneralHelper::getFormTypeById($id),
							'sectionClass' => Helpers::getComponent('admin-listing')['componentClass'],
							'emptyContent' => \esc_html__('Your form is not used in any location!', 'eightshift-forms'),
							'additionalAttributes' => [
								UtilsHelper::getStateAttribute('adminIntegrationType') => $type,
							],
						],
						'components',
						false,
						'admin-listing/partials'
					),
				],
				$debug
			)
		);
	}
}
