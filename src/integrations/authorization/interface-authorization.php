<?php
/**
 * Authorization_Interface interface.
 *
 * @package Eightshift_Forms\Integrations\Authorization
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Integrations\Authorization;

/**
 * Authorization_Interface interface.
 */
interface Authorization_Interface {

  /**
   * Generates a hash.
   *
   * @param  array  $params Request params we're verifying.
   * @param  string $salt   Salt used to generate the hash.
   * @return string
   */
  public function generate_hash( array $params, string $salt ): string;

  /**
   * Verifies a hash.
   *
   * @param  string $hash   Hash we're verifying.
   * @param  array  $params Request params we're verifying.
   * @param  string $salt   Salt used to generate the hash.
   * @return string
   */
  public function verify_hash( string $hash, array $params, string $salt ): bool;
}
