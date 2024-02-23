<?php

/**
 * The class register route for public form submiting endpoint - Airtable
 *
 * @package EightshiftForms\Rest\Route\Integrations\Airtable
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Airtable;

use EightshiftForms\Integrations\Airtable\AirtableClientInterface;
use EightshiftForms\Integrations\Airtable\SettingsAirtable;
use EightshiftForms\Rest\Routes\AbstractTestApi;

/**
 * Class TestApiAirtableRoute
 */
class TestApiAirtableRoute extends AbstractTestApi
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsAirtable::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable for Airtable data.
	 *
	 * @var AirtableClientInterface
	 */
	protected $airtableClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param AirtableClientInterface $airtableClient Inject Airtable which holds Airtable connect data.
	 */
	public function __construct(AirtableClientInterface $airtableClient)
	{
		$this->airtableClient = $airtableClient;
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
		return $this->airtableClient->getTestApi();
	}
}
