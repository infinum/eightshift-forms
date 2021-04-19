<?php
/**
 * HMAC generator class.
 *
 * @package Eightshift_Forms\Integrations\Authorization
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Integrations\Authorization;

use Eightshift_Libs\Core\Service;
use EightshiftForms\Hooks\Filters;

/**
 * HMAC generator class.
 */
class HMAC implements Service, Authorization_Interface, Filters {

  const AUTHORIZATION_KEY = 'authorization_hmac';

  /**
   * Provide filter that allows the project to generate the hash.
   *
   * @return void
   */
  public function register() {
    \add_filter( self::AUTHORIZATION_GENERATOR, [ $this, 'generate_hash' ], 1, 2 );
  }

  /**
   * Generates a hmac hash.
   *
   * @param  array  $params Request params we're verifying.
   * @param  string $salt   Salt used to generate the hash.
   * @return string
   */
  public function generate_hash( array $params, string $salt ): string {
    return 'hmac ' . hash_hmac( 'sha512', (string) \wp_json_encode( $params ), $salt );
  }

  /**
   * Verifies the passed hmac hash is the same as the generated one.
   *
   * @param  string $hash   Hash we're verifying.
   * @param  array  $params Request params we're verifying.
   * @param  string $salt   Salt used to generate the hash.
   * @return bool
   */
  public function verify_hash( string $hash, array $params, string $salt ): bool {
    return $hash === $this->generate_hash( $params, $salt );
  }
}
