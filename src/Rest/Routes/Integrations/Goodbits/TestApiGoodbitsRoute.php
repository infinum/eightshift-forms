<?php

/**
 * The class register route for public form submiting endpoint - Goodbits
 *
 * @package EightshiftForms\Rest\Route\Integrations\Goodbits
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Goodbits;

use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Goodbits\SettingsGoodbits;
use EightshiftForms\Rest\Routes\AbstractTestApi;

/**
 * Class TestApiGoodbitsRoute
 */
class TestApiGoodbitsRoute extends AbstractTestApi
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/' . AbstractTestApi::ROUTE_PREFIX_TEST_API . '-' . SettingsGoodbits::SETTINGS_TYPE_KEY . '/';

	/**
	 * Instance variable for Goodbits data.
	 *
	 * @var ClientInterface
	 */
	protected $goodbitsClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ClientInterface $goodbitsClient Inject Goodbits which holds Goodbits connect data.
	 */
	public function __construct(ClientInterface $goodbitsClient)
	{
		$this->goodbitsClient = $goodbitsClient;
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
	 * Implement test action.
	 *
	 * @return mixed
	 */
	protected function testAction()
	{
		return $this->goodbitsClient->getTestApi();
	}
}
