<?php

/**
 * Pardot Oauth class.
 *
 * @package EightshiftForms\Integrations\Pardot
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Pardot;

use EightshiftForms\Hooks\Variables;
use EightshiftForms\Oauth\AbstractOauth;
use EightshiftForms\Config\Config;
use EightshiftForms\Helpers\ApiHelpers;
use EightshiftForms\Helpers\SettingsHelpers;

/**
 * OauthPardot class.
 */
class OauthPardot extends AbstractOauth
{
	/**
	 * Retry count for refresh token.
	 *
	 * @var int
	 */
	private int $refreshTokenRetryCounter = 0;

	/**
	 * Access token key.
	 */
	public const OAUTH_PARDOT_ACCESS_TOKEN_KEY = 'pardot-access-token';

	/**
	 * Refresh token key.
	 */
	public const OAUTH_PARDOT_REFRESH_TOKEN_KEY = 'pardot-refresh-token';

	/**
	 * Get Pardot data API URL (pi.pardot.com / pi.demo.pardot.com).
	 *
	 * @param string $path Path.
	 */
	public function getApiUrl(string $path): string
	{
		$host = $this->isSandbox() ? 'pi.demo.pardot.com' : 'pi.pardot.com';

		return "https://{$host}/{$path}";
	}

	/**
	 * Get Salesforce authorization URL.
	 */
	public function getOauthAuthorizeUrl(): string
	{
		$clientId = SettingsHelpers::getOptionWithConstant(Variables::getClientIdPardot(), SettingsPardot::SETTINGS_PARDOT_CLIENT_ID);

		return \add_query_arg(
			[
				'response_type' => 'code',
				'client_id' => $clientId,
				'redirect_uri' => $this->getRedirectUri(SettingsPardot::SETTINGS_TYPE_KEY),
			],
			$this->getSfAuthHost() . '/services/oauth2/authorize'
		);
	}

	/**
	 * Get access token exchange data.
	 *
	 * @param string $code Code.
	 *
	 * @return array<string, mixed>
	 */
	public function getOauthAccessTokenData(string $code): array
	{
		$clientId = SettingsHelpers::getOptionWithConstant(Variables::getClientIdPardot(), SettingsPardot::SETTINGS_PARDOT_CLIENT_ID);
		$clientSecret = SettingsHelpers::getOptionWithConstant(Variables::getClientSecretPardot(), SettingsPardot::SETTINGS_PARDOT_SECRET);

		return [
			'url' => $this->getSfAuthHost() . '/services/oauth2/token',
			'args' => \http_build_query([
				'grant_type' => 'authorization_code',
				'client_id' => $clientId,
				'client_secret' => $clientSecret,
				'redirect_uri' => $this->getRedirectUri(SettingsPardot::SETTINGS_TYPE_KEY),
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
		$clientId = SettingsHelpers::getOptionWithConstant(Variables::getClientIdPardot(), SettingsPardot::SETTINGS_PARDOT_CLIENT_ID);
		$clientSecret = SettingsHelpers::getOptionWithConstant(Variables::getClientSecretPardot(), SettingsPardot::SETTINGS_PARDOT_SECRET);
		$refreshToken = SettingsHelpers::getOptionValue(OauthPardot::OAUTH_PARDOT_REFRESH_TOKEN_KEY);

		return [
			'url' => $this->getSfAuthHost() . '/services/oauth2/token',
			'args' => \http_build_query([
				'grant_type' => 'refresh_token',
				'client_id' => $clientId,
				'client_secret' => $clientSecret,
				'refresh_token' => $refreshToken,
			]),
		];
	}

	/**
	 * Get access token.
	 *
	 * @param string $code Code.
	 */
	public function getAccessToken(string $code): bool
	{
		$data = $this->getOauthAccessTokenData($code);

		return $this->getToken($data);
	}

	/**
	 * Get refresh token.
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
	 * Check if token has expired (Salesforce returns INVALID_SESSION_ID).
	 *
	 * @param array<string, mixed> $body Body.
	 */
	public function hasTokenExpired(array $body): bool
	{
		return ($body['errorCode'] ?? '') === 'INVALID_SESSION_ID';
	}

	/**
	 * Exchange or refresh token via Salesforce.
	 *
	 * @param array<string, mixed> $data Data with 'url' and 'args'.
	 */
	private function getToken(array $data): bool
	{
		$response = \wp_remote_post(
			$data['url'],
			[
				'headers' => [
					'Content-Type' => 'application/x-www-form-urlencoded',
					'Accept' => 'application/json',
				],
				'body' => $data['args'],
			]
		);

		$details = ApiHelpers::getIntegrationApiResponseDetails(
			SettingsPardot::SETTINGS_TYPE_KEY,
			$response,
			$data['url'],
		);

		$code = $details[Config::IARD_CODE];
		$body = $details[Config::IARD_BODY];

		if (ApiHelpers::isSuccessResponse($code)) {
			\update_option(SettingsHelpers::getSettingName(OauthPardot::OAUTH_PARDOT_ACCESS_TOKEN_KEY), $body['access_token']);
			\update_option(SettingsHelpers::getSettingName(OauthPardot::OAUTH_PARDOT_REFRESH_TOKEN_KEY), $body['refresh_token']);

			return true;
		}

		return false;
	}

	/**
	 * Return Salesforce auth host (login.salesforce.com / test.salesforce.com).
	 */
	private function getSfAuthHost(): string
	{
		return $this->isSandbox()
			? 'https://test.salesforce.com'
			: 'https://login.salesforce.com';
	}

	/**
	 * Determine if sandbox mode is active.
	 */
	private function isSandbox(): bool
	{
		return SettingsHelpers::getOptionValue(SettingsPardot::SETTINGS_PARDOT_ENVIRONMENT_KEY) === 'sandbox';
	}
}
