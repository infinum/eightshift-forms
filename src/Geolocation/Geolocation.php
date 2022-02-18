<?php

/**
 * The file that is an Geolocation class.
 *
 * @package EightshiftForms\Geolocation;
 */

declare(strict_types=1);

namespace EightshiftForms\Geolocation;

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Services\ServiceInterface;

/**
 * Geolocation class.
 */
class Geolocation implements ServiceInterface, GeolocationInterface
{
	use SettingsHelper;

	/**
	 * Internal countries list stored in a variable for caching.
	 *
	 * @var array<string>
	 */
	private $countries = [];

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
	public const GEOLOCATION_IS_USER_LOCATED = 'es_geolocation_is_user_located';

	/**
	 * Register all the hooks.
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_filter('init', [$this, 'setLocationCookie']);
		\add_filter(self::GEOLOCATION_IS_USER_LOCATED, [$this, 'isUserGeolocated'], 10, 3);
	}

	/**
	 * Set geolocation cookie.
	 *
	 * @return void
	 */
	public function setLocationCookie(): void
	{
		// Skip admin.
		if (is_admin()) {
			return;
		}

		// Bailout if not in use.
		$isGeolocationSettingsGlobalValid = \apply_filters(SettingsGeolocation::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false);
		if (!$isGeolocationSettingsGlobalValid) {
			return;
		}

		// Add the ability to disable geolocation from an external source (generally used for GDPR purposes).
		$filterName = Filters::getGeolocationFilterName('disable');
		if (has_filter($filterName)) {
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
	 * @param array<string, mixed> $defaultLocations Default locations set to form..
	 * @param array<string, mixed> $additionalLocations Additional location set to form.
	 *
	 * @return string
	 */
	public function isUserGeolocated(string $formId, array $defaultLocations, array $additionalLocations): string
	{
		// Bailout if not in use.
		$isGeolocationSettingsGlobalValid = \apply_filters(SettingsGeolocation::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false);
		if (!$isGeolocationSettingsGlobalValid) {
			return $formId;
		}

		// Add ability to disable geolocation from external source. (Generaly used for GDPR).
		$filterName = Filters::getGeolocationFilterName('disable');
		if (has_filter($filterName)) {
			Helper::logger([
				'geolocation' => 'Filter disabled active, skip geolocation.',
				'formIdOriginal' => $formId,
				'formIdUsed' => $formId,
				'userLocation' => '',
			]);
			return $formId;
		}

		// Returns user location retrieved from the API or cookie.
		$userLocation = $this->getLocationDetails();

		// Check if additional location exists on the form.
		if ($additionalLocations) {
			$matchAdditionalLocations = [];

			// Iterate all additional locations to find the first that matches.
			foreach ($additionalLocations as $additionalLocation) {
				if (!isset($additionalLocation['geoLocation'])) {
					continue;
				}

				// Find geolocation from array of options.
				$geoLocation = array_filter(
					$additionalLocation['geoLocation'],
					function ($geo) use ($userLocation) {
						$country = $this->getCountryGroup($geo['value']);

						return isset($country[$userLocation]);
					}
				) ?? [];

				// Exit after first succesfull result.
				if ($geoLocation) {
					$matchAdditionalLocations = $additionalLocation;
					break;
				}
			}

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

		// If there is no location but we have the default locations set match that array with the user location.
		if ($defaultLocations) {
			$matchDefaultLocations = array_filter(
				$defaultLocations,
				function ($location) use ($userLocation) {
					$country = $this->getCountryGroup($location['value']);

					return isset($country[$userLocation]);
				}
			);

			// Return first result.
			$matchDefaultLocations = reset($matchDefaultLocations);

			// If default locations match output that new form.
			if ($matchDefaultLocations) {
				Helper::logger([
					'geolocation' => 'Locations doesn\'t match or exist, default location match. Outputing new form.',
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

		// Final fallback if the user has no locations, no default locations or they didn't match. Just return the current form.
		Helper::logger([
			'geolocation' => 'Final fallback that returns the current form. Outputing the original form.',
			'formIdOriginal' => $formId,
			'formIdUsed' => $formId,
			'userLocation' => $userLocation,
		]);
		return $formId;
	}

	/**
	 * Get all country lists from the manifest.json.
	 *
	 * @return array<mixed>
	 */
	public function getCountries(): array
	{
		$output = [
			[
				'label' => __('Europe', 'eightshift-forms'),
				'value' => 'europe',
				'group' => [
					'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR',
					'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK',
					'SI', 'ES', 'SE', 'AL', 'AD', 'AM', 'BY', 'BA', 'FO', 'GE', 'GI', 'IS',
					'IM', 'XK', 'LI', 'MK', 'MD', 'MC', 'NO', 'RU', 'SM', 'RS', 'CH', 'TR',
					'UA', 'GB', 'VA',
				],
			],
			[
				'label' => __('European Union', 'eightshift-forms'),
				'value' => 'european-union',
				'group' => [
					'BE', 'EL', 'LT', 'PT', 'BG', 'ES', 'LU', 'RO', 'CZ',
					'FR', 'HU', 'SI', 'DK', 'HR', 'MT', 'SK', 'DE', 'IT',
					'NL', 'FI', 'EE', 'CY', 'AT', 'SE', 'IE', 'LV', 'PL',
				],
			],
			[
				'label' => __('Ex Yugoslavia', 'eightshift-forms'),
				'value' => 'ex-yugoslavia',
				'group' => [
					'HR', 'RS', 'BA', 'ME', 'SI', 'MK'
				],
			],
		];

		// Save to internal cache so we don't read manifest all the time.
		if (!$this->countries) {
			$this->countries = Components::getManifest(__DIR__);
		}

		foreach ($this->countries as $country) {
			$code = $country['Code'];

			$output[] = [
				'label' => $country['Name'],
				'value' => $code,
				'group' => [
					strtoupper($code),
				],
			];
		}

		// Provide custom countries.
		$filterName = Filters::getGeolocationFilterName('countries');
		if (has_filter($filterName)) {
			return apply_filters($filterName, $output ?? []);
		}

		return $output;
	}

	/**
	 * Get Country code group
	 *
	 * @param string $value Code name to check.
	 *
	 * @return array<string>
	 */
	private function getCountryGroup(string $value): array
	{
		$country = array_filter(
			$this->getCountries(),
			static function ($item) use ($value) {
				$itemValue = $item['value'] ?? '';
				return $itemValue === $value;
			}
		) ?? [];

		if (!$country) {
			return [];
		}

		$country = reset($country);

		if (!isset($country['group'])) {
			return [];
		}

		return array_flip($country['group']) ?? [];
	}

	/**
	 * Get current country, based on the IP address.
	 *
	 * @return string
	 */
	private function getLocationDetails(): string
	{
		if (getenv('TEST_GEOLOCATION')) {
			return (string) getenv('TEST_GEOLOCATION');
		}

		// Set country code manually for development purposes.
		if (Variables::getGeolocation()) {
			return Variables::getGeolocation();
		}

		// Filter provides user location from external source.
		$filterName = Filters::getGeolocationFilterName('userLocation');
		if (has_filter($filterName)) {
			return \apply_filters($filterName, '');
		}

		// Check cookie for country code.
		if (isset($_COOKIE[self::GEOLOCATION_COOKIE])) {
			return wp_kses_post(wp_unslash($_COOKIE[self::GEOLOCATION_COOKIE]));
		}

		$ipAddr = '';

		// Find user's remote address.
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$ipAddr = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP); //phpcs:ignore
		}

		// Skip if empty for some reason or if you are on local host.
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
