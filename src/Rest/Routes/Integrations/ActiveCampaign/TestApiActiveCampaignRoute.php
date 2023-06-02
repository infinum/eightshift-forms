<?php

/**
 * The class register route for public form submiting endpoint - ActiveCampaign
 *
 * @package EightshiftForms\Rest\Route\Integrations\ActiveCampaign
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\ActiveCampaign;

use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\ActiveCampaign\SettingsActiveCampaign;
use EightshiftForms\Rest\Routes\AbstractTestApi;

/**
 * Class TestApiActiveCampaignRoute
 */
class TestApiActiveCampaignRoute extends AbstractTestApi
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = SettingsActiveCampaign::SETTINGS_TYPE_KEY;

	/**
	 * Instance variable for ActiveCampaign data.
	 *
	 * @var ClientInterface
	 */
	protected $activeCampaignClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ClientInterface $activeCampaignClient Inject ActiveCampaign which holds ActiveCampaign connect data.
	 */
	public function __construct(ClientInterface $activeCampaignClient)
	{
		$this->activeCampaignClient = $activeCampaignClient;
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
		return $this->activeCampaignClient->getTestApi();
	}
}
