<?php

/**
 * NationBuilder Oauth class.
 *
 * @package EightshiftForms\Integrations\Nationbuilder
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Nationbuilder;

use EightshiftForms\Hooks\Variables;
use EightshiftForms\Oauth\AbstractOauth;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Helpers\SettingsHelpers;

/**
 * OauthNationbuilder class.
 */
class OauthNationbuilder extends AbstractOauth
{
	/**
	 * Retry count for refresh token.
	 *
	 * @var integer
	 */
	private $refreshTokenRetryCounter = 0;

	/**
	 * Access token key.
	 */
	public const OAUTH_NATIONBUILDER_ACCESS_TOKEN_KEY = 'nationbuilder-access-token';

	/**
	 * Refresh token key.
	 */
	public const OAUTH_NATIONBUILDER_REFRESH_TOKEN_KEY = 'nationbuilder-refresh-token';

	/**
	 * Get Oauth URL based on the provider Id.
	 *
	 * @param string $path Path.
	 *
	 * @return string
	 */
	public function getApiUrl(string $path): string
	{
		$clientSlug = SettingsHelpers::getOptionWithConstant(Variables::getClientSlugNationBuilder(), SettingsNationbuilder::SETTINGS_NATIONBUILDER_CLIENT_SLUG);

		return "https://{$clientSlug}.nationbuilder.com/{$path}";
	}

	/**
	 * Get authorization URL based on the provider Id.
	 *
	 * @return string
	 */
	public function getOauthAuthorizeUrl(): string
	{
		$clientId = SettingsHelpers::getOptionWithConstant(Variables::getClientIdNationBuilder(), SettingsNationbuilder::SETTINGS_NATIONBUILDER_CLIENT_ID);

		return \add_query_arg(
			[
				'response_type' => 'code',
				'client_id' => $clientId,
				'redirect_uri' => $this->getRedirectUri(SettingsNationbuilder::SETTINGS_TYPE_KEY),
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
		$clientId = SettingsHelpers::getOptionWithConstant(Variables::getClientIdNationBuilder(), SettingsNationbuilder::SETTINGS_NATIONBUILDER_CLIENT_ID);
		$clientSecret = SettingsHelpers::getOptionWithConstant(Variables::getClientSecretNationBuilder(), SettingsNationbuilder::SETTINGS_NATIONBUILDER_CLIENT_SECRET);

		return [
			'url' => $this->getApiUrl('oauth/token'),
			'args' => \wp_json_encode([
				'grant_type' => 'authorization_code',
				'client_id' => $clientId,
				'client_secret' => $clientSecret,
				'redirect_uri' => $this->getRedirectUri(SettingsNationbuilder::SETTINGS_TYPE_KEY),
				'code' => $code,
			]),
		];
	}

	/**
	 * Get refresh token data.
	 *
	 * @return array<string, mixed>
	 */
	public function getOauthRefreshTokenData(): array
	{
		$clientId = SettingsHelpers::getOptionWithConstant(Variables::getClientIdNationBuilder(), SettingsNationbuilder::SETTINGS_NATIONBUILDER_CLIENT_ID);
		$clientSecret = SettingsHelpers::getOptionWithConstant(Variables::getClientSecretNationBuilder(), SettingsNationbuilder::SETTINGS_NATIONBUILDER_CLIENT_SECRET);
		$refreshToken = SettingsHelpers::getOptionValue(OauthNationbuilder::OAUTH_NATIONBUILDER_REFRESH_TOKEN_KEY);

		return [
			'url' => $this->getApiUrl('oauth/token'),
			'args' => \wp_json_encode([
				'grant_type' => 'refresh_token',
				'refresh_token' => $refreshToken,
				'client_id' => $clientId,
				'client_secret' => $clientSecret,
			]),
		];
	}

	/**
	 * Get access token.
	 *
	 * @param string $code Code.
	 *
	 * @return boolean
	 */
	public function getAccessToken(string $code): bool
	{
		$data = $this->getOauthAccessTokenData($code);

		return $this->getToken($data);
	}

	/**
	 * Get refresh token.
	 *
	 * @return boolean
	 */
	public function getRefreshToken(): bool
	{
		if ($this->refreshTokenRetryCounter >= 3) {
			return false;
		}

		$token = $this->getToken($this->getOauthRefreshTokenData());

		if (!$token) {
			$this->refreshTokenRetryCounter++;
			return false;
		}

		$this->refreshTokenRetryCounter = 0;
		return true;
	}

	/**
	 * Check if token has expired.
	 *
	 * @param array<string, mixed> $body Body.
	 *
	 * @return boolean
	 */
	public function hasTokenExpired(array $body): bool
	{
		return ($body['code'] ?? '') === 'token_expired';
	}

	/**
	 * Get refresh token.
	 *
	 * @param array<string, mixed> $data Data.
	 *
	 * @return boolean
	 */
	private function getToken(array $data): bool
	{
		// Get Access token.
		$response = \wp_remote_post(
			$data['url'],
			[
				'headers' => [
					'Content-Type' => 'application/json',
					'Accept' => 'application/json',
				],
				'body' => $data['args'],
			]
		);

		// Structure response details.
		$details = ApiHelpers::getIntegrationApiResponseDetails(
			SettingsNationbuilder::SETTINGS_TYPE_KEY,
			$response,
			$data['url'],
		);

		$code = $details[Config::IARD_CODE];
		$body = $details[Config::IARD_BODY];

		// On success return output.
		if ($code >= Config::API_RESPONSE_CODE_SUCCESS && $code <= Config::API_RESPONSE_CODE_SUCCESS_RANGE) {
			\update_option(SettingsHelpers::getSettingName(OauthNationbuilder::OAUTH_NATIONBUILDER_ACCESS_TOKEN_KEY), $body['access_token']);
			\update_option(SettingsHelpers::getSettingName(OauthNationbuilder::OAUTH_NATIONBUILDER_REFRESH_TOKEN_KEY), $body['refresh_token']);

			return true;
		}

		return false;
	}
}
