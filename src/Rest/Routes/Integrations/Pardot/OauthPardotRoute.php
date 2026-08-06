<?php

/**
 * OAuth callback route for Pardot.
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Pardot
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Pardot;

use EightshiftForms\Integrations\Pardot\SettingsPardot;
use EightshiftForms\Oauth\OauthInterface;
use EightshiftForms\Rest\Routes\AbstractOauth;

/**
 * Class OauthPardotRoute
 */
class OauthPardotRoute extends AbstractOauth
{
	/**
	 * Route slug.
	 */
	public const ROUTE_SLUG = 'pardot';

	/**
	 * Create a new instance that injects classes
	 *
	 * @param OauthInterface $oauthPardot Inject Oauth methods.
	 */
	public function __construct(protected OauthInterface $oauthPardot)
	{
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
	 */
	protected function getOauthType(): string
	{
		return SettingsPardot::SETTINGS_TYPE_KEY;
	}

	/**
	 * Get the oauth allow key.
	 */
	protected function getOauthAllowKey(): string
	{
		return SettingsPardot::SETTINGS_PARDOT_OAUTH_ALLOW_KEY;
	}

	/**
	 * Check if the route is admin protected.
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
		$response = $this->oauthPardot->getAccessToken($code);

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
