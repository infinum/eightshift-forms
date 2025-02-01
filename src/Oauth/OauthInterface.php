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
	 * Get authorization URL based on the provider Id.
	 *
	 * @return string
	 */
	public function getOauthAuthorizeUrl(): string;
}
