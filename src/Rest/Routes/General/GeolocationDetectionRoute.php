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
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param GeolocationInterface $geolocation Inject geolocation which holds data about for storing to geolocation.
	 */
	public function __construct(
		ValidatorInterface $validator,
		LabelsInterface $labels,
		GeolocationInterface $geolocation,
	) {
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
	 * @return array<string, string>
	 */
	protected function getMandatoryParams(): array
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
	 * @return array<string, mixed>
	 */
	protected function submitAction(array $params): array
	{
		// Bailout if geolocation setting is off.
		if (!\apply_filters(SettingsGeolocation::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			return [
				AbstractBaseRoute::R_MSG => $this->labels->getLabel('geolocationSkipCheck'),
				AbstractBaseRoute::R_DEBUG => [
					AbstractBaseRoute::R_DEBUG_KEY => 'geolocationFeatureDisabled',
				],
			];
		}

		$data = EncryptionHelpers::decryptor($params['data'] ?? '');

		$dataOutput = \json_decode($data, true);

		if (!\is_array($dataOutput) && !$dataOutput) {
			throw new BadRequestException(
				$this->labels->getLabel('geolocationMalformedOrNotValid'),
				[
					AbstractBaseRoute::R_DEBUG_KEY => 'geolocationMalformedOrNotValidData',
				]
			);
		}

		$formId = $dataOutput['id'] ?? '';
		$geo = $dataOutput['geo'] ?? [];
		$alt = $dataOutput['alt'] ?? [];

		$geolocation = $this->geolocation->isUserGeolocated($formId, $geo, $alt);

		if (!$geolocation) {
			throw new BadRequestException(
				$this->labels->getLabel('geolocationMalformedOrNotValid'),
				[
					AbstractBaseRoute::R_DEBUG => $dataOutput,
					AbstractBaseRoute::R_DEBUG_KEY => 'geolocationMalformedOrNotValidData',
				]
			);
		}

		return [
			AbstractBaseRoute::R_MSG => $this->labels->getLabel('geolocationSuccess'),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG_KEY => 'geolocationSuccess',
			],
			AbstractBaseRoute::R_DATA => [
				UtilsHelper::getStateResponseOutputKey('geoId') => $geolocation,
			],
		];
	}
}
