<?php

/**
 * The class register route for public form submitting endpoint - OAuth Callback for Nationbuilder.
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Nationbuilder
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Nationbuilder;

use EightshiftForms\Integrations\Nationbuilder\SettingsNationbuilder;
use EightshiftForms\Oauth\OauthInterface;
use EightshiftForms\Rest\Routes\AbstractOauth;

/**
 * Class OauthNationbuilderRoute
 */
class OauthNationbuilderRoute extends AbstractOauth
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'nationbuilder';

	/**
	 * Instance variable for Oauth.
	 *
	 * @var OauthInterface
	 */
	protected $oauthNationbuilder;

	/**
	 * Create a new instance that injects classes
	 *
	 * @param OauthInterface $oauthNationbuilder Inject Oauth methods.
	 */
	public function __construct(OauthInterface $oauthNationbuilder)
	{
		$this->oauthNationbuilder = $oauthNationbuilder;
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
		return SettingsNationbuilder::SETTINGS_TYPE_KEY;
	}

	/**
	 * Get the oauth allow key.
	 *
	 * @return string
	 */
	protected function getOauthAllowKey(): string
	{
		return SettingsNationbuilder::SETTINGS_NATIONBUILDER_OAUTH_ALLOW_KEY;
	}

	/**
	 * Check if the route is admin protected.
	 *
	 * @return boolean
	 */
	protected function isRouteAdminProtected(): bool
	{
		return false;
	}

	/**
	 * Get mandatory params.
	 *
	 * @param array<string, mixed> $params Params passed from the request.
	 *
	 * @return array<string, string>
	 */
	protected function getMandatoryParams(array $params): array
	{
		return [];
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
		$response = $this->oauthNationbuilder->getAccessToken($code);

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
