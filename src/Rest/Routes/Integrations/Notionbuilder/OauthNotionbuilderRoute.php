<?php

/**
 * The class register route for public form submiting endpoint - OAuth Callback for Notionbuilder.
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Notionbuilder
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Notionbuilder;

use EightshiftForms\Integrations\Notionbuilder\SettingsNotionbuilder;
use EightshiftForms\Oauth\OauthInterface;
use EightshiftForms\Rest\Routes\AbstractOauth;

/**
 * Class OauthNotionbuilderRoute
 */
class OauthNotionbuilderRoute extends AbstractOauth
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'notionbuilder';

	/**
	 * Instance variable for Oauth.
	 *
	 * @var OauthInterface
	 */
	protected $oauthNotionbuilder;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param OauthInterface $oauthNotionbuilder Inject Oauth methods.
	 */
	public function __construct(OauthInterface $oauthNotionbuilder)
	{
		$this->oauthNotionbuilder = $oauthNotionbuilder;
	}

	/**
	 * Get the base url of the route
	 *
	 * @return string The base URL for route you are adding.
	 */
	protected function getRouteName(): string
	{
		return '/' . AbstractOauth::ROUTE_PREFIX_OAUTH_API . '/' . self::ROUTE_SLUG;
	}

	/**
	 * Get the oauth type.
	 *
	 * @return string
	 */
	protected function getOauthType(): string
	{
		return SettingsNotionbuilder::SETTINGS_TYPE_KEY;
	}

	/**
	 * Get the oauth allow key.
	 *
	 * @return string
	 */
	protected function getOauthAllowKey(): string
	{
		return SettingsNotionbuilder::SETTINGS_NOTIONBUILDER_OAUTH_ALLOW_KEY;
	}

	/**
	 * Implement submit action.
	 *
	 * @param string $code The code.
	 *
	 * @return mixed
	 */
	protected function submitAction(string $code)
	{
		$response = $this->oauthNotionbuilder->getAccessToken($code);

		if ($response) {
			$this->redirect(
				\esc_html__('Oauth connection successful', 'eightshift-forms'),
			);
		}

		$this->redirect(
			\esc_html__('Oauth connection failed', 'eightshift-forms'),
		);
	}
}
