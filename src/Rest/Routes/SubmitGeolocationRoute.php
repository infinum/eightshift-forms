<?php

/**
 * The class register route for public form submitting endpoint - Captcha
 *
 * @package EightshiftForms\Rest\Routes
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes;

use EightshiftForms\Geolocation\GeolocationInterface;
use EightshiftForms\Geolocation\SettingsGeolocation;
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Helpers\EncryptionHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
use Throwable;
use WP_REST_Request;

/**
 * Class SubmitGeolocationRoute
 */
class SubmitGeolocationRoute extends AbstractBaseRoute
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
	 * Instance variable of labels data.
	 *
	 * @var LabelsInterface
	 */
	protected LabelsInterface $labels;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param GeolocationInterface $geolocation Inject geolocation which holds data about for storing to geolocation.
	 * @param LabelsInterface $labels Inject labels methods.
	 */
	public function __construct(
		GeolocationInterface $geolocation,
		LabelsInterface $labels
	) {
		$this->geolocation = $geolocation;
		$this->labels = $labels;
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

		// Bailout if geolocation setting is off.
		if (!\apply_filters(SettingsGeolocation::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			return \rest_ensure_response(
				ApiHelpers::getApiSuccessPublicOutput(
					$this->labels->getLabel('geolocationSkipCheck'),
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
					ApiHelpers::getApiErrorPublicOutput(
						$this->labels->getLabel('geolocationMalformedOrNotValid'),
						[],
						$debug
					)
				);
			}

			$params = EncryptionHelpers::decryptor($data);

			if (!Helpers::isJson($params)) {
				return \rest_ensure_response(
					ApiHelpers::getApiErrorPublicOutput(
						$this->labels->getLabel('geolocationMalformedOrNotValid'),
						[],
						$debug
					)
				);
			}

			$params = \json_decode($params, true);

			if (!\is_array($params) && !$params) {
				return \rest_ensure_response(
					ApiHelpers::getApiErrorPublicOutput(
						$this->labels->getLabel('geolocationMalformedOrNotValid'),
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
				ApiHelpers::getApiSuccessPublicOutput(
					$this->labels->getLabel('geolocationSuccess'),
					[
						UtilsHelper::getStateResponseOutputKey('geoId') => $geolocation,
					],
					$debug
				)
			);
		} catch (Throwable $t) {
			return \rest_ensure_response(
				ApiHelpers::getApiErrorPublicOutput(
					$this->labels->getLabel('geolocationMalformedOrNotValid'),
					[],
					\array_merge(
						$debug,
						[
							'exception' => $t,
						]
					)
				)
			);
		}
	}
}
