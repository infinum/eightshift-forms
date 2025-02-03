<?php

/**
 * Interface that holds all methods for getting forms Oauth connection.
 *
 * @package EightshiftForms\Oauth
 */

declare(strict_types=1);

namespace EightshiftForms\Oauth;

/**
 * Interface for OauthInterface
 */
interface OauthInterface
{
	/**
	 * Get Oauth URL based on the provider Id.
	 *
	 * @param string $path Path.
	 *
	 * @return string
	 */
	public function getApiUrl(string $path): string;

	/**
	 * Get authorization URL based on the provider Id.
	 *
	 * @return string
	 */
	public function getOauthAuthorizeUrl(): string;

	/**
	 * Get access token data.
	 *
	 * @param string $code Code.
	 *
	 * @return array<string, mixed>
	 */
	public function getOauthAccessTokenData(string $code): array;

	/**
	 * Get refresh token data.
	 *
	 * @return array<string, mixed>
	 */
	public function getOauthRefreshTokenData(): array;

	/**
	 * Get access token.
	 *
	 * @param string $code Code.
	 *
	 * @return boolean
	 */
	public function getAccessToken(string $code): bool;

	/**
	 * Get refresh token.
	 *
	 * @return boolean
	 */
	public function getRefreshToken(): bool;

	/**
	 * Check if token has expired.
	 *
	 * @param array<string, mixed> $body Body.
	 *
	 * @return boolean
	 */
	public function hasTokenExpired(array $body): bool;
}
