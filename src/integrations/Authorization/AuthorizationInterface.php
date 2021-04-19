<?php

/**
 * AuthorizationInterface interface.
 *
 * @package EightshiftForms\Integrations\Authorization
 */

declare(strict_types=1);

namespace EightshiftForms\Integrations\Authorization;

/**
 * AuthorizationInterface interface.
 */
interface AuthorizationInterface
{

  /**
   * Generates a hash.
   *
   * @param  array  $params Request params we're verifying.
   * @param  string $salt   Salt used to generate the hash.
   * @return string
   */
	public function generate_hash(array $params, string $salt): string;

  /**
   * Verifies a hash.
   *
   * @param  string $hash   Hash we're verifying.
   * @param  array  $params Request params we're verifying.
   * @param  string $salt   Salt used to generate the hash.
   * @return bool
   */
	public function verify_hash(string $hash, array $params, string $salt): bool;
}
