<?php

/**
 * OAuth2Client interface.
 *
 * @package EightshiftForms\Integrations
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations;

/**
 * OAuth2Client interface.
 */
interface OAuth2ClientInterface
{

	/**
	 * Returns the access token, either from cache or fetches a new one.
	 *
	 * @param  string $tokenKey        Token's transient key.
	 * @param  bool   $shouldFetchNew Pass true to skip fetching content for transient. Useful for when you want to make sure your access token nis fresh.
	 * @return string
	 */
	public function getToken(string $tokenKey, bool $shouldFetchNew = false): string;

	/**
	 * Set credentials, used when we can't set credentials during DI services building.
	 *
	 * @param  array $credentials OAuth2 credentials.
	 * @return void
	 */
	public function setCredentials(array $credentials): void;
}
