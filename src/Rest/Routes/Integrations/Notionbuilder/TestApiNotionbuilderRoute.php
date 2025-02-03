<?php

/**
 * The class register route for public form submiting endpoint - Notionbuilder
 *
 * @package EightshiftForms\Rest\Route\Integrations\Notionbuilder
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Notionbuilder;

use EightshiftForms\Integrations\Notionbuilder\NotionbuilderClientInterface;
use EightshiftForms\Integrations\Notionbuilder\SettingsNotionbuilder;
use EightshiftForms\Rest\Routes\AbstractTestApi;

/**
 * Class TestApiNotionbuilderRoute
 */
class TestApiNotionbuilderRoute extends AbstractTestApi
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsNotionbuilder::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable for Workable data.
	 *
	 * @var NotionbuilderClientInterface
	 */
	protected $notionbuilderClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param NotionbuilderClientInterface $notionbuilderClient Inject Workable which holds Workable connect data.
	 */
	public function __construct(NotionbuilderClientInterface $notionbuilderClient)
	{
		$this->notionbuilderClient = $notionbuilderClient;
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
		return $this->notionbuilderClient->getTestApi();
	}
}
