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
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Helpers\EncryptionHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Troubleshooting\SettingsFallback;
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
	 * Instance variable of geolocation data.
	 *
	 * @var GeolocationInterface
	 */
	protected GeolocationInterface $geolocation;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param SecurityInterface $security Inject security methods.
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param GeolocationInterface $geolocation Inject geolocation which holds data about for storing to geolocation.
	 */
	public function __construct(
		SecurityInterface $security,
		ValidatorInterface $validator,
		LabelsInterface $labels,
		GeolocationInterface $geolocation,
	) {
		$this->security = $security;
		$this->validator = $validator;
		$this->labels = $labels;
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
	 *
	 * @return boolean
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
				$this->getLabels()->getLabel('geolocationSkipCheck'),
				[
					AbstractBaseRoute::R_DEBUG => $params,
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_GEOLOCATION_FEATURE_DISABLED,
				],
			);
			// phpcs:enable
		}

		$data = EncryptionHelpers::decryptor($params['data'] ?? '');

		if (!$data) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				$this->getLabels()->getLabel('geolocationMalformedOrNotValid'),
				[
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_GEOLOCATION_MALFORMED_DECRYPT_DATA,
				]
			);
			// phpcs:enable
		}

		$dataOutput = \json_decode($data, true);

		if (!\is_array($dataOutput) && !$dataOutput) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				$this->getLabels()->getLabel('geolocationMalformedOrNotValid'),
				[
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_GEOLOCATION_MALFORMED_DECRYPT_DATA,
				]
			);
			// phpcs:enable
		}

		$formId = $dataOutput['id'] ?? '';
		$geo = $dataOutput['geo'] ?? [];
		$alt = $dataOutput['alt'] ?? [];

		$geolocation = $this->geolocation->isUserGeolocated($formId, $geo, $alt);

		if (!$geolocation) {
			// phpcs:disable Eightshift.Security.HelpersEscape.ExceptionNotEscaped
			throw new BadRequestException(
				$this->getLabels()->getLabel('geolocationMalformedOrNotValid'),
				[
					AbstractBaseRoute::R_DEBUG => $dataOutput,
					AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_GEOLOCATION_DETECTION_FAILED,
				]
			);
			// phpcs:enable
		}

		return [
			AbstractBaseRoute::R_MSG => $this->getLabels()->getLabel('geolocationSuccess'),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG_KEY => SettingsFallback::SETTINGS_FALLBACK_FLAG_GEOLOCATION_SUCCESS,
			],
			AbstractBaseRoute::R_DATA => [
				UtilsHelper::getStateResponseOutputKey('geoId') => $geolocation,
			],
		];
	}
}
