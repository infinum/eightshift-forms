<?php

/**
 * The file that is an Geolocation class.
 *
 * @package EightshiftForms\Geolocation;
 */

declare(strict_types=1);

namespace EightshiftForms\Geolocation;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Variables;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Geolocation class.
 */
class Geolocation implements ServiceInterface
{
	/**
	 * Geolocation cookie name const.
	 *
	 * @var string
	 */
	public const GEOLOCATION_COOKIE = 'esForms-country';

	/**
	 * Geolocation check if user is geolocated constant.
	 *
	 * @var string
	 */
	public const GEOLOCATION_IS_USER_LOCATED = 'es_geolocation_is_use_located';

	/**
	 * Register all the hooks.
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter('init', [$this, 'setLoactionCookie']);
		\add_filter(self::GEOLOCATION_IS_USER_LOCATED, [$this, 'isUserGeolocated'], 10, 3);
	}

	/**
	 * Set geolocation cookie on init load.
	 *
	 * @return void
	 */
	public function setLoactionCookie(): void
	{
		// Skip admin.
		if (is_admin()) {
			return;
		}

		// If cookie exists don't set it again.
		if (isset($_COOKIE[self::GEOLOCATION_COOKIE])) {
			return;
		}

		setcookie(
			self::GEOLOCATION_COOKIE,
			$this->getLocationDetails(),
			time() + DAY_IN_SECONDS,
			'/'
		);
	}

	/**
	 * Check if user location exists in the provided locations.
	 *
	 * @param string $formId Form Id.
	 * @param array<string> $defaultLocations Default locations set to form..
	 * @param array<string> $additionalLocations Additional location set to form.
	 *
	 * @return string
	 */
	public function isUserGeolocated(string $formId, array $defaultLocations, array $additionalLocations): string
	{
		// Returns user location got from the API or Cookie.
		$userLocation = $this->getLocationDetails();

		// Check if additional location exists on the form.
		if ($additionalLocations) {
			$matchAdditionalLocations = array_filter(
				$additionalLocations,
				static function($location) use ($userLocation) {
					return array_filter(
						$location['geoLocation'], 
						static function($geo) use ($userLocation) {
							return strtoupper($geo['value']) === $userLocation;
						}
					);
				}
			);

			// Return first result.
			$matchAdditionalLocations = reset($matchAdditionalLocations);

			// If additional locations match output that new form. 
			if ($matchAdditionalLocations) {
				Helper::logger([
					'geolocation' => 'Locations exists, locations match. Outputing new form.',
					'formIdOriginal' => $formId,
					'formIdUsed' => $matchAdditionalLocations['formId'] ?? '',
					'userLocation' => $userLocation,
				]);
				return $matchAdditionalLocations['formId'] ?? '';
			}
		}

		// If thare are not location but we have default locations set match that array with the user location.
		if ($defaultLocations) {
			$matchDefaultLocations = array_filter(
				$defaultLocations,
				static function($location) use ($userLocation) {
					return strtoupper($location['value']) === $userLocation;
				}
			);
	
			// Return first result.
			$matchDefaultLocations = reset($matchDefaultLocations);
	
			// If default locations match output that new form. 
			if ($matchDefaultLocations) {
				Helper::logger([
					'geolocation' => 'Locations doesn\'t exists, default location match. Outputing new form.',
					'formIdOriginal' => $formId,
					'formIdUsed' => $formId,
					'userLocation' => $userLocation,
				]);
				return $formId;
			}

			// If we have set default locations but no match return empty form.
			Helper::logger([
				'geolocation' => 'Locations doesn\'t exists, default location doesn\'t match. Outputing nothing.',
				'formIdOriginal' => $formId,
				'formIdUsed' => '',
				'userLocation' => $userLocation,
			]);
			return '';
		}

		// Final fallback if the user has no locations, no default locations or thery don't match just return the current form.
		Helper::logger([
			'geolocation' => 'Final fallback that returns current form. Outputing original form.',
			'formIdOriginal' => $formId,
			'formIdUsed' => $formId,
			'userLocation' => $userLocation,
		]);
		return $formId;
	}

	/**
	 * Get current country, based on the IP address.
	 *
	 * @return string
	 */
	private function getLocationDetails(): string
	{
		// Set country code manually for develop.
		if (Variables::getGeolocation()) {
			return Variables::getGeolocation();
		}

		// Check cookie for country code.
		if (isset($_COOKIE[self::GEOLOCATION_COOKIE])) {
			return wp_kses_post(wp_unslash($_COOKIE[self::GEOLOCATION_COOKIE]));
		}

		$ipAddr = '';

		// Find users remote address.
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$ipAddr = $_SERVER['REMOTE_ADDR']; //phpcs:ignore
		}

		// Skip if empty for some reason or you are on local host.
		if ($ipAddr !== '127.0.0.1' && $ipAddr !== '::1' && !empty($ipAddr)) {
			try {

				// Get data from the local DB.
				require_once __DIR__ . '/geoip2.phar';

				$reader = new \GeoIp2\Database\Reader(__DIR__ . '/geoip.mmdb'); // @phpstan-ignore-line
				$record = $reader->country($ipAddr); // @phpstan-ignore-line
				$cookieCountry = $record->country;

				if (!empty($cookieCountry)) {
					return strtoupper($cookieCountry->isoCode);
				}

				return '';
			} catch (\Throwable $th) {
				return 'ERROR: ' . $th->getMessage();
			}
		} else {
			return 'localhost';
		}
	}
}
