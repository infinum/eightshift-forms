<?php

/**
 * The file that is an Geolocation Interface class.
 *
 * @package EightshiftForms\Geolocation;
 */

declare(strict_types=1);

namespace EightshiftForms\Geolocation;

/**
 * GeolocationInterface class.
 */
interface GeolocationInterface
{
	/**
	 * Get all countrie lists from the manifest.json.
	 *
	 * @return array<string>
	 */
	public function getCountries(): array;
}
