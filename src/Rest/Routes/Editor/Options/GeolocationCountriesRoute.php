<?php

/**
 * The class register route for getting Geolocation list of countries endpoint.
 *
 * @package EightshiftForms\Rest\Routes\Editor\Options;
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Editor\Options;

use EightshiftForms\Exception\UnverifiedRequestException;
use EightshiftForms\Geolocation\GeolocationInterface;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Rest\Routes\AbstractUtilsBaseRoute;
use WP_REST_Request;

/**
 * Class GeolocationCountriesRoute
 */
class GeolocationCountriesRoute extends AbstractUtilsBaseRoute
{
	/**
	 * Route slug
	 *
	 * @var string
	 */
	public const ROUTE_SLUG = 'geolocation-countries';

	/**
	 * Instance variable of ClientInterface data.
	 *
	 * @var GeolocationInterface
	 */
	private $geolocation;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param GeolocationInterface $geolocation Inject GeolocationInterface which holds Geolocation data.
	 */
	public function __construct(GeolocationInterface $geolocation)
	{
		$this->geolocation = $geolocation;
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
	 * Returns allowed methods for this route.
	 *
	 * @return string
	 */
	protected function getMethods(): string
	{
		return static::READABLE;
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
		$debug = [
			'request' => $request,
		];

		try {
			return \rest_ensure_response(
				UtilsApiHelper::getApiSuccessPublicOutput(
					\esc_html__('Success.', 'eightshift-forms'),
					$this->geolocation->getCountriesList(),
					$debug
				)
			);
		} catch (UnverifiedRequestException $e) {
			// Die if any of the validation fails.
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					$e->getMessage(),
					\array_merge(
						$debug,
						[
							'exception' => $e->getMessage(),
						]
					)
				)
			);
		}
	}
}
