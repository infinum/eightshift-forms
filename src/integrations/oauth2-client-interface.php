<?php
/**
 * OAuth2_Client interface.
 *
 * @package Eightshift_Forms\Integrations
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Integrations;

/**
 * OAuth2_Client interface.
 */
interface OAuth2_Client_Interface {

  /**
   * Returns the access token, either from cache or fetches a new one.
   *
   * @param  string $token_key        Token's transient key.
   * @param  bool   $should_fetch_new Pass true to skip fetching content for transient. Useful for when you want to make sure your access token nis fresh.
   * @return string
   */
  public function get_token( string $token_key, bool $should_fetch_new = false): string;

  /**
   * Set credentials, used when we can't set credentials during DI services building.
   *
   * @param  array $credentials OAuth2 credentials.
   * @return void
   */
  public function set_credentials( array $credentials): void;
}
