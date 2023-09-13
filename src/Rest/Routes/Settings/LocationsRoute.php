<?php

/**
 * The class to provide forms locations usage.
 *
 * @package EightshiftForms\Rest\Routes\Settings
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Settings;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Location\SettingsLocationInterface;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use WP_REST_Request;

/**
 * Class LocationsRoute
 */
class LocationsRoute extends AbstractBaseRoute
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'locations';

	/**
	 * Instance variable for HubSpot form data.
	 *
	 * @var SettingsLocationInterface
	 */
	protected $settingsLocation;

	/**
	 * Create a new instance.
	 *
	 * @param SettingsLocationInterface $settingsLocation Inject IntegrationSyncDiff which holds sync data.
	 */
	public function __construct(SettingsLocationInterface $settingsLocation)
	{
		$this->settingsLocation = $settingsLocation;
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
	 * Get callback arguments array
	 *
	 * @return array<string, mixed> Either an array of options for the endpoint, or an array of arrays for multiple methods.
	 */
	protected function getCallbackArguments(): array
	{
		return [
			'methods' => $this->getMethods(),
			'callback' => [$this, 'routeCallback'],
			'permission_callback' => [$this, 'permissionCallback'],
		];
	}

	/**
	 * Returns allowed methods for this route.
	 *
	 * @return string
	 */
	protected function getMethods(): string
	{
		return static::CREATABLE;
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

		$id = (string) $request->get_param('id');

		$debug = [
			'request' => $request,
		];

		return \rest_ensure_response(
			$this->getApiSuccessOutput(
				\esc_html__('Success', 'eightshift-forms'),
				[
					'output' => Components::renderPartial('component', 'admin-listing', 'item-details', [
						'items' => $this->settingsLocation->getBlockLocations($id),
						'type' => Helper::getFormTypeById($id),
						'sectionClass' => Components::getComponent('admin-listing')['componentClass'],
						'emptyContent' => \esc_html__('Your form is not used in any location!', 'eightshift-forms')
					]),
				],
				$debug
			)
		);
	}
}
