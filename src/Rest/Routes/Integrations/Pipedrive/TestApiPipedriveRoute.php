<?php

/**
 * The class register route for public form submiting endpoint - Pipedrive
 *
 * @package EightshiftForms\Rest\Route\Integrations\Pipedrive
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Pipedrive;

use EightshiftForms\Integrations\Pipedrive\PipedriveClientInterface;
use EightshiftForms\Integrations\Pipedrive\SettingsPipedrive;
use EightshiftForms\Rest\Routes\AbstractTestApi;

/**
 * Class TestApiPipedriveRoute
 */
class TestApiPipedriveRoute extends AbstractTestApi
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsPipedrive::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable for Pipedrive data.
	 *
	 * @var PipedriveClientInterface
	 */
	protected $pipedriveClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param PipedriveClientInterface $pipedriveClient Inject Pipedrive which holds Pipedrive connect data.
	 */
	public function __construct(PipedriveClientInterface $pipedriveClient)
	{
		$this->pipedriveClient = $pipedriveClient;
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
		return $this->pipedriveClient->getTestApi();
	}
}
