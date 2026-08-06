<?php

/**
 * The class register route for public form submitting endpoint - geolocation detection.
 *
 * @package EightshiftForms\Rest\Routes\General;
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\General;

use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Geolocation\GeolocationInterface;
use EightshiftForms\Geolocation\SettingsGeolocation;
use EightshiftForms\Helpers\EncryptionHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Labels\Labels;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class GeolocationDetectionRoute
 */
class GeolocationDetectionRoute extends AbstractSimpleFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'geolocation';

	/**
	 * Create a new instance that injects classes
	 *
	 * @param SecurityInterface $security Inject security methods.
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param GeolocationInterface $geolocation Inject geolocation which holds data about for storing to geolocation.
	 */
	public function __construct(
		SecurityInterface $security,
		ValidatorInterface $validator,
		/**
		 * Instance variable of geolocation data.
		 */
		protected GeolocationInterface $geolocation,
	) {
		$this->security = $security;
		$this->validator = $validator;
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
	 * Get mandatory params.
	 *
	 * @param array<string, mixed> $params Params passed from the request.
	 *
	 * @return array<string, string>
	 */
	protected function getMandatoryParams(array $params): array
	{
		return [
			'data' => 'string',
		];
	}

	/**
	 * Check if the route is admin protected.
	 */
	protected function isRouteAdminProtected(): bool
	{
		return false;
	}

	/**
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $params Prepared params.
	 *
	 * @throws BadRequestException If geolocation is malformed or not valid.
	 *
	 * @return array<string, mixed>
	 */
	protected function submitAction(array $params): array
	{
		// Bailout if geolocation setting is off.
		if (!\apply_filters(SettingsGeolocation::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				Labels::getLabel(Labels::LABEL_GEOLOCATION_SKIP_CHECK),
				[
					AbstractBaseRoute::R_DEBUG => $params,
					AbstractBaseRoute::R_DEBUG_KEY => Labels::LABEL_GEOLOCATION_FEATURE_DISABLED,
				],
			);
			// phpcs:enable
		}

		$data = EncryptionHelpers::decryptor($params['data'] ?? '');

		if (!$data) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				Labels::getLabel(Labels::LABEL_GEOLOCATION_MALFORMED_OR_NOT_VALID),
				[
					AbstractBaseRoute::R_DEBUG_KEY => Labels::LABEL_GEOLOCATION_MALFORMED_DECRYPT_DATA,
				]
			);
			// phpcs:enable
		}

		$dataOutput = \json_decode($data, true);

		if (!\is_array($dataOutput) && !$dataOutput) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				Labels::getLabel(Labels::LABEL_GEOLOCATION_MALFORMED_OR_NOT_VALID),
				[
					AbstractBaseRoute::R_DEBUG_KEY => Labels::LABEL_GEOLOCATION_MALFORMED_DECRYPT_DATA,
				]
			);
			// phpcs:enable
		}

		$formId = $dataOutput['id'] ?? '';
		$geo = $dataOutput['geo'] ?? [];
		$alt = $dataOutput['alt'] ?? [];

		$geolocation = $this->geolocation->isUserGeolocated($formId, $geo, $alt);

		if ($geolocation === '' || $geolocation === '0') {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				Labels::getLabel(Labels::LABEL_GEOLOCATION_MALFORMED_OR_NOT_VALID),
				[
					AbstractBaseRoute::R_DEBUG => $dataOutput,
					AbstractBaseRoute::R_DEBUG_KEY => Labels::LABEL_GEOLOCATION_DETECTION_FAILED,
				]
			);
			// phpcs:enable
		}

		return [
			AbstractBaseRoute::R_MSG => Labels::getLabel(Labels::LABEL_GEOLOCATION_SUCCESS),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG_KEY => Labels::LABEL_GEOLOCATION_SUCCESS,
			],
			AbstractBaseRoute::R_DATA => [
				UtilsHelper::getStateResponseOutputKey('geoId') => $geolocation,
			],
		];
	}
}
