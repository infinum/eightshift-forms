<?php

/**
 * The class register route for public form submiting endpoint - Hubspot
 *
 * @package EightshiftForms\Rest\Route\Integrations\Hubspot
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Hubspot;

use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Hubspot\SettingsHubspot;
use EightshiftForms\Rest\Routes\AbstractTestApi;

/**
 * Class TestApiHubspotRoute
 */
class TestApiHubspotRoute extends AbstractTestApi
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsHubspot::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable for Hubspot data.
	 *
	 * @var ClientInterface
	 */
	protected $hubspotClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ClientInterface $hubspotClient Inject Hubspot which holds Hubspot connect data.
	 */
	public function __construct(ClientInterface $hubspotClient)
	{
		$this->hubspotClient = $hubspotClient;
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
		return $this->hubspotClient->getTestApi();
	}
}
