<?php

/**
 * The class register route for public form submitting endpoint - Goodbits
 *
 * @package EightshiftForms\Rest\Route\Integrations\Goodbits
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Goodbits;

use EightshiftForms\Config\Config;
use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Goodbits\SettingsGoodbits;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftLibs\Rest\Routes\AbstractRoute;

/**
 * Class TestApiGoodbitsRoute
 */
class TestApiGoodbitsRoute extends AbstractSimpleFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsGoodbits::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable for Goodbits data.
	 *
	 * @var ClientInterface
	 */
	protected $goodbitsClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param SecurityInterface $security Inject security methods.
	 * @param ValidatorInterface $validator Inject validator methods.
	 * @param LabelsInterface $labels Inject labels methods.
	 * @param ClientInterface $goodbitsClient Inject Goodbits which holds Goodbits connect data.
	 */
	public function __construct(
		SecurityInterface $security,
		ValidatorInterface $validator,
		LabelsInterface $labels,
		ClientInterface $goodbitsClient
	) {
		$this->security = $security;
		$this->validator = $validator;
		$this->labels = $labels;
		$this->goodbitsClient = $goodbitsClient;
	}

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/' . Config::ROUTE_PREFIX_TEST_API . '/' . self::ROUTE_SLUG;
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
			'type' => 'string',
		];
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
	 * Implement submit action.
	 *
	 * @param array<string, mixed> $params Prepared params.
	 *
	 * @return array<string, mixed>
	 */
	protected function submitAction(array $params): array
	{
		$output = $this->goodbitsClient->getTestApi();

		if ($output[Config::IARD_STATUS] === AbstractRoute::STATUS_ERROR) {
			throw new BadRequestException(
				$this->getLabels()->getLabel('testApiError'),
				[
					AbstractBaseRoute::R_DEBUG => $output,
					AbstractBaseRoute::R_DEBUG_KEY => 'testApiError',
				]
			);
		}

		return [
			AbstractBaseRoute::R_MSG => $this->getLabels()->getLabel('testApiSuccess'),
			AbstractBaseRoute::R_DEBUG => [
				AbstractBaseRoute::R_DEBUG => $output,
				AbstractBaseRoute::R_DEBUG_KEY => 'testApiSuccess',
			],
		];
	}
}
