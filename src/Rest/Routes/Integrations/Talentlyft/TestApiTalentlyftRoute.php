<?php

/**
 * The class register route for public form submitting endpoint - Talentlyft
 *
 * @package EightshiftForms\Rest\Route\Integrations\Talentlyft
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Talentlyft;

use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Talentlyft\SettingsTalentlyft;
use EightshiftForms\Rest\Routes\AbstractTestApi;

/**
 * Class TestApiTalentlyftRoute
 */
class TestApiTalentlyftRoute extends AbstractTestApi
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsTalentlyft::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable for Talentlyft data.
	 *
	 * @var ClientInterface
	 */
	protected $talentlyftClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ClientInterface $talentlyftClient Inject Talentlyft which holds Talentlyft connect data.
	 */
	public function __construct(ClientInterface $talentlyftClient)
	{
		$this->talentlyftClient = $talentlyftClient;
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
		return $this->talentlyftClient->getTestApi();
	}
}
