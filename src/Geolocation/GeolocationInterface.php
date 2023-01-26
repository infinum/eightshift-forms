<?php

/**
 * Geolocation interface.
 *
 * @package EightshiftForms\Geolocation;
 */

declare(strict_types=1);

namespace EightshiftForms\Geolocation;

/**
 * GeolocationInterface interface.
 */
interface GeolocationInterface
{
	/**
	 * Get all country lists from the manifest.json.
	 *
	 * @return array<string>
	 */
	public function getCountriesList(): array;

	/**
	 * Get geolocation cookie name.
	 *
	 * @return string
	 */
	public function getGeolocationCookieName(): string;
}
