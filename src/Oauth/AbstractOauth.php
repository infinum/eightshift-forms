<?php

/**
 * Oauth class.
 *
 * @package EightshiftForms\Oauth
 */

declare(strict_types=1);

namespace EightshiftForms\Oauth;

use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;

/**
 * Oauth class.
 */
abstract class AbstractOauth implements OauthInterface
{
	/**
	 * Get authorization URL based on the provider Id.
	 *
	 * @return string
	 */
	public abstract function getOauthAuthorizeUrl(): string;

	/**
	 * Get redirect URI based on the provider Id.
	 *
	 * @param string $type Type.
	 *
	 * @return string
	 */
	public function getRedirectUri(string $type): string
	{
		$namespace = UtilsConfig::ROUTE_NAMESPACE;
		$version = UtilsConfig::ROUTE_VERSION;

		return rest_url("{$namespace}/{$version}/oauth/{$type}");
	}
}
