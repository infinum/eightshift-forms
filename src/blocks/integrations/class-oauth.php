<?php
/**
 * Blocks class used to define configurations for blocks.
 *
 * @package Eightshift_Forms\Blocks
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Blocks;

/**
 * OAuth class which handles access token connections.
 */
class OAuth2 {

  const HOUR_IN_SECONDS = '3600';

  /**
   * Returns the access token, either from cache or fetches a new one.
   *
   * @param  string $token_key        Token's transient key.
   * @param  bool   $should_fetch_new Pass true to skip fetching content for transient. Useful for when you want to make sure your access token nis fresh.
   * @return string
   */
  public function get_token(string $token_key, bool $should_fetch_new = false): string {
    if (!$should_fetch_new) {
      $token = $this->get_token_from_cache($token_key);
    }

    if ($should_fetch_new || empty( $token )) {
      $token = $this->fetch_token( $token_key );
    }

    return $token;
  }

  /**
   * Fetches token from provider using Client Credentials method
   * https://oauth.net/2/grant-types/client-credentials/
   *
   * @return string
   */
  protected function fetch_token() {
    
  }

  /**
   * Returns token from currently implemented cache.
   *
   * @param  string $token_key  Token's transient key.
   * @param  string $token      Token's value.
   * @param  int    $expiration Cache's expiration in seconds.
   * @return bool
   */
  protected function save_token_to_cache(string $token_key, string $token, int $expiration = self::HOUR_IN_SECONDS): bool {
    return set_transient( $token_key, $token, $expiration );
  }

  /**
   * Returns token from currently implemented cache.
   *
   * @param  string $token_key Token's transient key.
   * @return string
   */
  protected function get_token_from_cache(string $token_key): string {
    return get_transient( $token_key );
  }
}
