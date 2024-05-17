<?php

/**
 * The class register route for public form submiting endpoint - Captcha
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Geolocation\GeolocationInterface;
use EightshiftForms\Geolocation\SettingsGeolocation;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsEncryption;
use EightshiftFormsVendor\EightshiftFormsUtils\Rest\Routes\AbstractUtilsBaseRoute;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
use Throwable;
use WP_REST_Request;

/**
 * Class SubmitGeolocationRoute
 */
class SubmitGeolocationRoute extends AbstractUtilsBaseRoute
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'geolocation';

	/**
	 * Instance variable of geolocation data.
	 *
	 * @var GeolocationInterface
	 */
	protected GeolocationInterface $geolocation;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param GeolocationInterface $geolocation Inject geolocation which holds data about for storing to geolocation.
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
	 * Method that returns WP REST Response
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

		// Bailout if troubleshooting "skip captcha" is on.
		if (!\apply_filters(SettingsGeolocation::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			return \rest_ensure_response(
				UtilsApiHelper::getApiSuccessPublicOutput(
					\esc_html__('Form captcha skipped due to troubleshooting config set in settings.', 'eightshift-forms'),
					[],
					$debug
				)
			);
		}

		try {
			$params = $this->prepareSimpleApiParams($request);

			$data = $params['data'] ?? '';
			if (!\is_string($data)) {
				return \rest_ensure_response(
					UtilsApiHelper::getApiErrorPublicOutput(
						\esc_html__('The geolocation data is malformed or not valid.', 'eightshift-forms'),
						[],
						$debug
					)
				);
			}

			$params = UtilsEncryption::decryptor($data);

			if (!Helpers::isJson($params)) {
				return \rest_ensure_response(
					UtilsApiHelper::getApiErrorPublicOutput(
						\esc_html__('The geolocation data is malformed or not valid.', 'eightshift-forms'),
						[],
						$debug
					)
				);
			}

			$params = \json_decode($params, true);

			if (!\is_array($params) && !$params) {
				return \rest_ensure_response(
					UtilsApiHelper::getApiErrorPublicOutput(
						\esc_html__('The geolocation data is malformed or not valid.', 'eightshift-forms'),
						[],
						$debug
					)
				);
			}

			$formId = $params['id'] ?? '';
			$geo = $params['geo'] ?? [];
			$alt = $params['alt'] ?? [];

			$geolocation = $this->geolocation->isUserGeolocated($formId, $geo, $alt);

			return \rest_ensure_response(
				UtilsApiHelper::getApiSuccessPublicOutput(
					\esc_html__('Success geolocation', 'eightshift-forms'),
					[
						'formId' => $geolocation,
					],
					$debug
				)
			);
		} catch (Throwable $t) {
			return \rest_ensure_response(
				UtilsApiHelper::getApiErrorPublicOutput(
					\esc_html__('The geolocation data is malformed or not valid.', 'eightshift-forms'),
					[],
					\array_merge(
						$debug,
						[
							'exeption' => $t,
						]
					)
				)
			);
		}
	}
}
