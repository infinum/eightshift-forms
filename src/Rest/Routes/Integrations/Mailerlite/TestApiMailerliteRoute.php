<?php

/**
 * The class register route for public form submiting endpoint - Mailerlite
 *
 * @package EightshiftForms\Rest\Route\Integrations\Mailerlite
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Mailerlite;

use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Mailerlite\SettingsMailerlite;
use EightshiftForms\Rest\Routes\AbstractTestApi;

/**
 * Class TestApiMailerliteRoute
 */
class TestApiMailerliteRoute extends AbstractTestApi
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsMailerlite::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable for Mailerlite data.
	 *
	 * @var ClientInterface
	 */
	protected $mailerliteClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ClientInterface $mailerliteClient Inject Mailerlite which holds Mailerlite connect data.
	 */
	public function __construct(ClientInterface $mailerliteClient)
	{
		$this->mailerliteClient = $mailerliteClient;
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
		return $this->mailerliteClient->getTestApi();
	}
}
