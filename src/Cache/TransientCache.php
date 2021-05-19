<?php

/**
 * TransientCache class.
 *
 * @package EightshiftForms\Cache
 */

declare(strict_types=1);

namespace EightshiftForms\Cache;

/**
 * TransientCache class.
 */
class TransientCache implements Cache
{

	/**
	 * Saves some data in cache.
	 *
	 * @param  string $cacheKey  Where to save the data.
	 * @param  string $data       Data to save in cache, preferably json_encoded.
	 * @param  int    $expiration (Optional) Cache expiration time.
	 * @return bool
	 */
	public function save(string $cacheKey, string $data, int $expiration = 3600): bool
	{
		return set_transient($cacheKey, $data, $expiration);
	}

	/**
	 * Returns specific cache.
	 *
	 * @param  string $cacheKey Which cache to read.
	 * @return string
	 */
	public function get(string $cacheKey): string
	{
		return (string) get_transient($cacheKey);
	}

	/**
	 * Check if specific cache exists.
	 *
	 * @param  string $cacheKey Cache's key.
	 * @return bool
	 */
	public function exists(string $cacheKey): bool
	{
		return ! empty(get_transient($cacheKey));
	}

	/**
	 * Set credentials, used when we can't set credentials during DI services building.
	 *
	 * @param  string $routeName   Route's name.
	 * @param  string $routeUri    Route's URI.
	 * @param  array  $routeParams Route params.
	 * @return string
	 */
	public function calculateCacheKeyForRequest(string $routeName, string $routeUri, array $routeParams): string
	{
		return "route-cache-$routeName-" . hash('crc32', $routeUri . wp_json_encode($routeParams));
	}
}
