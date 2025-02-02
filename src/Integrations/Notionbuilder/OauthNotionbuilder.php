<?php

/**
 * NotionBuilder Oauth class.
 *
 * @package EightshiftForms\Integrations\Notionbuilder
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Notionbuilder;

use EightshiftForms\Hooks\Variables;
use EightshiftForms\Oauth\AbstractOauth;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsSettingsHelper;

/**
 * OauthNotionbuilder class.
 */
class OauthNotionbuilder extends AbstractOauth
{
	/**
	 * Access token key.
	 */
	public const OAUTH_NOTIONBUILDER_ACCESS_TOKEN_KEY = 'notionbuilder-access-token';

	/**
	 * Refresh token key.
	 */
	public const OAUTH_NOTIONBUILDER_REFRESH_TOKEN_KEY = 'notionbuilder-refresh-token';

	/**
	 * Get Oauth URL based on the provider Id.
	 *
	 * @param string $type Type.
	 *
	 * @return string
	 */
	public function getApiUrl(string $path): string
	{
		$clientSlug = UtilsSettingsHelper::getOptionWithConstant(Variables::getClientSlugNotionBuilder(), SettingsNotionbuilder::SETTINGS_NOTIONBUILDER_CLIENT_SLUG);

		return "https://{$clientSlug}.nationbuilder.com/{$path}";
	}

	/**
	 * Get authorization URL based on the provider Id.
	 *
	 * @return string
	 */
	public function getOauthAuthorizeUrl(): string
	{
		$clientId = UtilsSettingsHelper::getOptionWithConstant(Variables::getClientIdNotionBuilder(), SettingsNotionbuilder::SETTINGS_NOTIONBUILDER_CLIENT_ID);

		return \add_query_arg(
			[
				'response_type' => 'code',
				'client_id' => $clientId,
				'redirect_uri' => $this->getRedirectUri(SettingsNotionbuilder::SETTINGS_TYPE_KEY),
			],
			$this->getApiUrl('oauth/authorize')
		);
	}

	/**
	 * Get access token data.
	 *
	 * @param string $code Code.
	 *
	 * @return array<string, mixed>
	 */
	public function getOauthAccessTokenData(string $code): array
	{
		$clientId = UtilsSettingsHelper::getOptionWithConstant(Variables::getClientIdNotionBuilder(), SettingsNotionbuilder::SETTINGS_NOTIONBUILDER_CLIENT_ID);
		$clientSecret = UtilsSettingsHelper::getOptionWithConstant(Variables::getClientSecretNotionBuilder(), SettingsNotionbuilder::SETTINGS_NOTIONBUILDER_CLIENT_SECRET);

		return [
			'url' => $this->getApiUrl('oauth/token'),
			'args' => \wp_json_encode([
				'grant_type' => 'authorization_code',
				'client_id' => $clientId,
				'client_secret' => $clientSecret,
				'redirect_uri' => $this->getRedirectUri(SettingsNotionbuilder::SETTINGS_TYPE_KEY),
				'code' => $code,
			]),
		];
	}
}
