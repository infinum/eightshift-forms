<?php

/**
 * The class register route for public form submitting endpoint - Workable
 *
 * @package EightshiftForms\Rest\Route\Integrations\Workable
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Workable;

use EightshiftForms\Config\Config;
use EightshiftForms\Exception\BadRequestException;
use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Workable\SettingsWorkable;
use EightshiftForms\Labels\LabelsInterface;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftForms\Rest\Routes\AbstractSimpleFormSubmit;
use EightshiftForms\Security\SecurityInterface;
use EightshiftForms\Validation\ValidatorInterface;
use EightshiftFormsVendor\EightshiftLibs\Rest\Routes\AbstractRoute;

/**
 * Class TestApiWorkableRoute
 */
class TestApiWorkableRoute extends AbstractSimpleFormSubmit
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsWorkable::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable for Workable data.
	 *
	 * @var ClientInterface
	 */
	protected $workableClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param SecurityInterface $security Inject security methods.
	 * @param ValidatorInterface $validator Inject validator.
	 * @param LabelsInterface $labels Inject labels.
	 * @param ClientInterface $workableClient Inject Workable which holds Workable connect data.
	 */
	public function __construct(
		SecurityInterface $security,
		ValidatorInterface $validator,
		LabelsInterface $labels,
		ClientInterface $workableClient
	) {
		$this->security = $security;
		$this->validator = $validator;
		$this->labels = $labels;
		$this->workableClient = $workableClient;
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
	 * @param array<string, mixed> $params Params passed from the request.
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
		$output = $this->workableClient->getTestApi();

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
