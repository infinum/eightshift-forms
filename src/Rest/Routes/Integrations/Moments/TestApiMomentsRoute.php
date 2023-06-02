<?php

/**
 * The class register route for public form submiting endpoint - Moments
 *
 * @package EightshiftForms\Rest\Route\Integrations\Moments
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Moments;

use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Moments\SettingsMoments;
use EightshiftForms\Rest\Routes\AbstractTestApi;

/**
 * Class TestApiMomentsRoute
 */
class TestApiMomentsRoute extends AbstractTestApi
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsMoments::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable for Moments data.
	 *
	 * @var ClientInterface
	 */
	protected $momentsClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ClientInterface $momentsClient Inject Moments which holds Moments connect data.
	 */
	public function __construct(ClientInterface $momentsClient)
	{
		$this->momentsClient = $momentsClient;
	}

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/' . AbstractTestApi::ROUTE_PREFIX_TEST_API . '/' . self::ROUTE_SLUG;
	}

	/**
	 * Implement test action.
	 *
	 * @return mixed
	 */
	protected function testAction()
	{
		return $this->momentsClient->getTestApi();
	}
}
