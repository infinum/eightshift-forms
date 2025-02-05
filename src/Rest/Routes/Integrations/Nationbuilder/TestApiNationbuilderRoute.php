<?php

/**
 * The class register route for public form submiting endpoint - Nationbuilder
 *
 * @package EightshiftForms\Rest\Route\Integrations\Nationbuilder
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Nationbuilder;

use EightshiftForms\Integrations\Nationbuilder\NationbuilderClientInterface;
use EightshiftForms\Integrations\Nationbuilder\SettingsNationbuilder;
use EightshiftForms\Rest\Routes\AbstractTestApi;

/**
 * Class TestApiNationbuilderRoute
 */
class TestApiNationbuilderRoute extends AbstractTestApi
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsNationbuilder::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable for Workable data.
	 *
	 * @var NationbuilderClientInterface
	 */
	protected $nationbuilderClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param NationbuilderClientInterface $nationbuilderClient Inject Workable which holds Workable connect data.
	 */
	public function __construct(NationbuilderClientInterface $nationbuilderClient)
	{
		$this->nationbuilderClient = $nationbuilderClient;
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
		return $this->nationbuilderClient->getTestApi();
	}
}
