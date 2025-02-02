<?php

/**
 * The class register route for public form submiting endpoint - OAuth Callback for Notionbuilder.
 *
 * @package EightshiftForms\Rest\Routes\Integrations\Notionbuilder
 */

declare(strict_types=1);

namespace EightshiftForms\Rest\Routes\Integrations\Notionbuilder;

use EightshiftForms\Integrations\Notionbuilder\OauthNotionbuilder;
use EightshiftForms\Integrations\Notionbuilder\SettingsNotionbuilder;
use EightshiftForms\Oauth\OauthInterface;
use EightshiftForms\Rest\Routes\AbstractOauth;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsApiHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;

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
	 * Returns allowed methods for this route.
	 *
	 * @return string
	 */
	protected function getMethods(): string
	{
		return static::READABLE;
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
	 * Implement submit action.
	 *
	 * @param string $code The code.
	 *
	 * @return mixed
	 */
	protected function submitAction(string $code)
	{
		// Get token data.
		$accessTokenData = $this->oauthNotionbuilder->getOauthAccessTokenData($code);

		// Get Access token.
		$accessTokenResponse = \wp_remote_post(
			$accessTokenData['url'],
			[
				'headers' => [
					'Content-Type' => 'application/json',
					'Accept' => 'application/json',
				],
				'body' => $accessTokenData['args'],
			]
		);

		// Structure response details.
		$accessTokenResponseDetails = UtilsApiHelper::getIntegrationApiReponseDetails(
			SettingsNotionbuilder::SETTINGS_TYPE_KEY,
			$accessTokenResponse,
			$accessTokenData['url'],
		);

		$accessTokenResponseCode = $accessTokenResponseDetails[UtilsConfig::IARD_CODE];
		$accessTokenResponseBody = $accessTokenResponseDetails[UtilsConfig::IARD_BODY];

		// On success return output.
		if ($accessTokenResponseCode >= UtilsConfig::API_RESPONSE_CODE_SUCCESS && $accessTokenResponseCode <= UtilsConfig::API_RESPONSE_CODE_SUCCESS_RANGE) {
			\update_option(UtilsSettingsHelper::getSettingName(OauthNotionbuilder::OAUTH_NOTIONBUILDER_ACCESS_TOKEN_KEY), $accessTokenResponseBody['access_token']);
			\update_option(UtilsSettingsHelper::getSettingName(OauthNotionbuilder::OAUTH_NOTIONBUILDER_REFRESH_TOKEN_KEY), $accessTokenResponseBody['refresh_token']);

			$this->redirect(
				\esc_html__('Oauth connection successful', 'eightshift-forms'),
			);
		}

		$this->redirect(
			\esc_html__('Oauth connection failed', 'eightshift-forms'),
		);
	}
}
