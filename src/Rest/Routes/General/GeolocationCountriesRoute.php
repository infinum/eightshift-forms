<?php

/**
 * The class register route for getting Geolocation list of countries endpoint.
 *
 * @package EightshiftForms\Rest\Routes\General;
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\General;

use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Geolocation\GeolocationInterface;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidatorInterface;

/**
 * Class GeolocationCountriesRoute
 */
class GeolocationCountriesRoute extends AbstractSimpleFormSubmit
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
	 * @param SecurityInterface $security Inject security methods.
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param GeolocationInterface $geolocation Inject GeolocationInterface which holds Geolocation data.
	 */
	public function __construct(
		SecurityInterface $security,
		ValidatorInterface $validator,
		LabelsInterface $labels,
		GeolocationInterface $geolocation
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
	 * Returns allowed methods for this route.
	 *
	 * @return string
	 */
	protected function getMethods(): string
	{
		return static::READABLE;
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
	 * Get mandatory params.
	 *
	 * @param array<string, mixed> $params Params passed from the request.
	 *
	 * @return array<string, string>
	 */
	protected function getMandatoryParams(array $params): array
	{
		return [];
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
		$countries = $this->geolocation->getCountriesList();

		if (!$countries) {
			throw new BadRequestException(
				$this->getLabels()->getLabel('geolocationCountriesMissing'),
				[
					AbstractBaseRoute::R_DEBUG_KEY => 'geolocationCountriesMissing',
				]
			);
		}

		return [
			AbstractBaseRoute::R_MSG => $this->getLabels()->getLabel('geolocationCountriesSuccess'),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG_KEY => 'geolocationCountriesSuccess',
			],
			AbstractBaseRoute::R_DATA => [
				UtilsHelper::getStateResponseOutputKey('geolocationCountries') => $countries,
			],
		];
	}
}
