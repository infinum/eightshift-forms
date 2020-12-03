<?php
/**
 * Cache interface.
 *
 * @package Eightshift_Forms\Cache
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Cache;

/**
 * Cache interface.
 */
interface Cache {

  /**
   * Saves some data in cache.
   *
   * @param  string $cache_key Where to save the data.
   * @param  string $data      Data to save in cache, preferably json_encoded.
   * @param  int    $expiration (Optional) Cache expiration time.
   * @return bool
   */
  public function save( string $cache_key, string $data, int $expiration = 3600 ): bool;

  /**
   * Returns specific cache.
   *
   * @param  string $cache_key Which cache to read.
   * @return string
   */
  public function get( string $cache_key ): string;

  /**
   * Check if specific cache exists.
   *
   * @param  string $cache_key        Cache's key.
   * @return bool
   */
  public function exists( string $cache_key ): bool;

  /**
   * Set credentials, used when we can't set credentials during DI services building.
   *
   * @param  string $route_name   Route's name.
   * @param  string $route_uri    Route's URI.
   * @param  array  $route_params Route params.
   * @return string
   */
  public function calculate_cache_key_for_request( string $route_name, string $route_uri, array $route_params ): string;
}
