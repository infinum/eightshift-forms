<?php

/**
 * Hmac generator class.
 *
 * @package EightshiftForms\Integrations\Authorization
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Authorization;

use EightshiftForms\Hooks\Filters;
use EightshiftLibs\Services\ServiceInterface;

/**
 * Hmac generator class.
 */
class Hmac implements ServiceInterface, AuthorizationInterface, Filters
{

	public const AUTHORIZATION_KEY = 'authorizationHmac';

	/**
	 * Provide filter that allows the project to generate the hash.
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter(self::AUTHORIZATION_GENERATOR, [$this, 'generateHash'], 1, 2);
	}

	/**
	 * Generates a hmac hash.
	 *
	 * @param  array  $params Request params we're verifying.
	 * @param  string $salt   Salt used to generate the hash.
	 * @return string
	 */
	public function generateHash(array $params, string $salt): string
	{
		return 'hmac ' . hash_hmac('sha512', (string) \wp_json_encode($params), $salt);
	}

	/**
	 * Verifies the passed hmac hash is the same as the generated one.
	 *
	 * @param  string $hash   Hash we're verifying.
	 * @param  array  $params Request params we're verifying.
	 * @param  string $salt   Salt used to generate the hash.
	 * @return bool
	 */
	public function verifyHash(string $hash, array $params, string $salt): bool
	{
		return $hash === $this->generateHash($params, $salt);
	}
}
