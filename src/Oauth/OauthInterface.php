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
	 */
	public function getApiUrl(string $path): string;

	/**
	 * Get authorization URL based on the provider Id.
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
	 */
	public function getAccessToken(string $code): bool;

	/**
	 * Get refresh token.
	 */
	public function getRefreshToken(): bool;

	/**
	 * Check if token has expired.
	 *
	 * @param array<string, mixed> $body Body.
	 */
	public function hasTokenExpired(array $body): bool;
}
