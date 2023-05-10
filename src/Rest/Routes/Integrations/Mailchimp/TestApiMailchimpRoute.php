<?php

/**
 * The class register route for public form submiting endpoint - Mailchimp
 *
 * @package EightshiftForms\Rest\Route\Integrations\Mailchimp
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Mailchimp;

use EightshiftForms\Integrations\ClientInterface;
use EightshiftForms\Integrations\Mailchimp\SettingsMailchimp;
use EightshiftForms\Rest\Routes\AbstractTestApi;

/**
 * Class TestApiMailchimpRoute
 */
class TestApiMailchimpRoute extends AbstractTestApi
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = '/' . AbstractTestApi::ROUTE_PREFIX_TEST_API . '-' . SettingsMailchimp::SETTINGS_TYPE_KEY . '/';

	/**
	 * Instance variable for Mailchimp data.
	 *
	 * @var ClientInterface
	 */
	protected $mailchimpClient;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param ClientInterface $mailchimpClient Inject Mailchimp which holds Mailchimp connect data.
	 */
	public function __construct(ClientInterface $mailchimpClient)
	{
		$this->mailchimpClient = $mailchimpClient;
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
		return $this->mailchimpClient->getTestApi();
	}
}
