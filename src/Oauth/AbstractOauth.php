<?php

/**
 * Oauth class.
 *
 * @package EightshiftForms\Oauth
 */

declare(strict_types=1);

namespace EightshiftForms\Oauth;

use EightshiftForms\Config\Config;

/**
 * Oauth class.
 */
abstract class AbstractOauth implements OauthInterface
{
	/**
	 * Get redirect URI based on the provider Id.
	 *
	 * @param string $type Type.
	 *
	 * @return string
	 */
	public function getRedirectUri(string $type): string
	{
		$namespace = Config::ROUTE_NAMESPACE;
		$version = Config::ROUTE_VERSION;

		return \rest_url("{$namespace}/{$version}/oauth/{$type}");
	}
}
