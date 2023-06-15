<?php

/**
 * The class register route for public form submiting endpoint - Workable
 *
 * @package EightshiftForms\Rest\Route\Integrations\Workable
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Workable;

use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Workable\SettingsWorkable;
use EightshiftForms\Rest\Routes\AbstractTestApi;

/**
 * Class TestApiWorkableRoute
 */
class TestApiWorkableRoute extends AbstractTestApi
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
	 * @param ClientInterface $workableClient Inject Workable which holds Workable connect data.
	 */
	public function __construct(ClientInterface $workableClient)
	{
		$this->workableClient = $workableClient;
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
		return $this->workableClient->getTestApi();
	}
}
