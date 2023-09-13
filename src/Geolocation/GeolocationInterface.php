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
	 * Detect users geolocation.
	 *
	 * @return string
	 */
	public function getUsersGeolocation(): string;

	/**
	 * Check if user location exists in the provided locations.
	 *
	 * @param string $formId Form Id.
	 * @param array<string, mixed> $defaultLocations Default locations set to form..
	 * @param array<string, mixed> $additionalLocations Additional location set to form.
	 *
	 * @return string
	 */
	public function isUserGeolocated(string $formId, array $defaultLocations, array $additionalLocations): string;
}
