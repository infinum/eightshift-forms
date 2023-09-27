<?php

/**
 * The file that is an Geolocation class.
 *
 * @package EightshiftForms\Geolocation;
 */

declare(strict_types=1);

namespace EightshiftForms\Geolocation;

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Hooks\Variables;
use EightshiftForms\Misc\SettingsCloudflare;
use EightshiftForms\Settings\SettingsHelper;
use EightshiftFormsVendor\EightshiftLibs\Geolocation\AbstractGeolocation;
use Exception;

/**
 * Geolocation class.
 */
class Geolocation extends AbstractGeolocation implements GeolocationInterface
{
	/**
	 * Use general helper trait.
	 */
	use SettingsHelper;

	/**
	 * Geolocation check if user is geolocated constant.
	 *
	 * @var string
	 */
	public const GEOLOCATION_IS_USER_LOCATED = 'es_geolocation_is_user_located';

	/**
	 * Register all the hooks
	 */
	public function register(): void
	{
		// Use normal geolocation detection from db.
		\add_action('init', [$this, 'setNormalLocationCookie']);

		\add_filter(self::GEOLOCATION_IS_USER_LOCATED, [$this, 'isUserGeolocated'], 10, 3);
	}

	/**
	 * Set geolocation cookie.
	 *
	 * @return void
	 */
	public function setNormalLocationCookie(): void
	{
		// Bailout if geolocation feature is not used.
		if (!$this->useGeolocation()) {
			return;
		}

		try {
			$cookieValue = $this->getUsersGeolocation();

			// Set cookie if we have a value.
			if ($cookieValue) {
				\ob_start();
				$this->setCookie(
					$this->getGeolocationCookieName(),
					$cookieValue,
					$this->getGeolocationExpiration(),
					'/'
				);
				\ob_end_flush();
			}
		} catch (Exception $exception) {
			/*
			 * The getGeolocation will throw an error if the phar or geo db files are missing,
			 * but if we threw an exception here, that would break the execution of the WP app.
			 * This way we'll log the exception, but the site should work fine without setting
			 * the cookie.
			 */
			\error_log("Error code: {$exception->getCode()}, with message: {$exception->getMessage()}"); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			return;
		}
	}

	/**
	 * Tooggle geolocation usage based on this flag.
	 *
	 * @return boolean
	 */
	public function useGeolocation(): bool
	{
		return \apply_filters(SettingsGeolocation::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false);
	}

	/**
	 * Get geolocation cookie name.
	 *
	 * @return string
	 */
	public function getGeolocationCookieName(): string
	{
		return 'esForms-country';
	}

	/**
	 * Get geolocation executable phar location.
	 *
	 * @throws Exception If file is missing in provided path.
	 *
	 * @return string
	 */
	public function getGeolocationPharLocation(): string
	{
		$path = Helper::getDataManifestPath('geolocation', 'geoip.phar');

		$filterName = Filters::getFilterName(['geolocation', 'pharLocation']);
		if (\has_filter($filterName)) {
			$path = \apply_filters($filterName, null);
		}

		if (!\file_exists($path)) {
			// translators: %s will be replaced with the phar location.
			throw new Exception(\sprintf(\esc_html__('Missing Geolocation phar on this location %s', 'eightshift-libs'), $path));
		}

		return $path;
	}

	/**
	 * Get geolocation database location.
	 *
	 * @throws Exception If file is missing in provided path.
	 *
	 * @return string
	 */
	public function getGeolocationDbLocation(): string
	{
		$path = Helper::getDataManifestPath('geolocation', 'geoip.mmdb');

		$filterName = Filters::getFilterName(['geolocation', 'dbLocation']);
		if (\has_filter($filterName)) {
			$path = \apply_filters($filterName, null);
		}

		if (!\file_exists($path)) {
			// translators: %s will be replaced with the database location.
			throw new Exception(\sprintf(\esc_html__('Missing Geolocation database on this location %s', 'eightshift-libs'), $path));
		}

		return $path;
	}

	/**
	 * Gets the list of all countries from the manifest.
	 *
	 * @return array<mixed>
	 */
	public function getCountriesList(): array
	{
		$filterName = Filters::getFilterName(['geolocation', 'countriesList']);

		if (\has_filter($filterName)) {
			return \apply_filters($filterName, $this->getCountries());
		}

		return $this->getCountries();
	}

	/**
	 * Gets an IP address manually. Generally used for development and testing.
	 *
	 * @return string
	 */
	public function getIpAddress(): string
	{
		return Variables::getGeolocationIp();
	}

	/**
	 * Get geolocation expiration time.
	 *
	 * @return int
	 */
	public function getGeolocationExpiration(): int
	{
		return \time() + 1296000; // 15 days.
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
		// Bailout if geolocation feature is not used.
		if (!$this->useGeolocation()) {
			return $formId;
		}

		// Returns user location retrieved from the API or cookie.
		$userLocation = $this->getUsersGeolocation();

		// Check if additional location exists on the form.
		if ($additionalLocations) {
			$matchAdditionalLocations = [];

			// Iterate all additional locations to find the first that matches.
			foreach ($additionalLocations as $additionalLocation) {
				if (!isset($additionalLocation['geoLocation'])) {
					continue;
				}

				// Find geolocation from array of options.
				$geoLocation = \array_filter(
					$additionalLocation['geoLocation'] ?? [],
					function ($geo) use ($userLocation) {
						$country = $this->getCountryGroup($geo ?? '');
						return isset($country[$userLocation]);
					}
				);

				// Exit after first successful result.
				if ($geoLocation) {
					$matchAdditionalLocations = $additionalLocation;
					break;
				}
			}

			// If additional locations match output that new form.
			if ($matchAdditionalLocations) {
				return $matchAdditionalLocations['formId'] ?? '';
			}
		}

		// If there is no location but we have the default locations set match that array with the user location.
		if ($defaultLocations) {
			$matchDefaultLocations = \array_filter(
				$defaultLocations,
				function ($location) use ($userLocation) {
					$country = $this->getCountryGroup($location);

					return isset($country[$userLocation]);
				}
			);

			// Return first result.
			$matchDefaultLocations = \reset($matchDefaultLocations);

			// If default locations match output that new form.
			if ($matchDefaultLocations) {
				return $formId;
			}

			// If we have set default locations but no match return empty form.
			return '';
		}

		// Final fallback if the user has no locations, no default locations or they didn't match. Just return the current form.
		return $formId;
	}

	/**
	 * Detect users geolocation.
	 *
	 * @return string
	 */
	public function getUsersGeolocation(): string
	{
		// Bailout if geolocation feature is not used.
		if (!$this->useGeolocation()) {
			return '';
		}

		// Use Cloudflare header if that feature is used.
		if ($this->isOptionCheckboxChecked(SettingsCloudflare::SETTINGS_CLOUDFLARE_USE_KEY, SettingsCloudflare::SETTINGS_CLOUDFLARE_USE_KEY)) {
			$outputCloudflare = isset($_SERVER['HTTP_CF_IPCOUNTRY']) ? $this->cleanCookieValue($_SERVER['HTTP_CF_IPCOUNTRY']) : ''; // phpcs:ignore

			if ($outputCloudflare) {
				return $outputCloudflare;
			}
		}

		// Check if cookie is set and return that value.
		if (isset($_COOKIE[$this->getGeolocationCookieName()])) {
			$outputCookie = $this->cleanCookieValue($_COOKIE[$this->getGeolocationCookieName()]); // phpcs:ignore

			if ($outputCookie) {
				return $outputCookie;
			}
		}

		return $this->cleanCookieValue($this->getGeolocation());
	}

	/**
	 * Clean cookie value.
	 *
	 * @param string $value Cookie value to clean.
	 *
	 * @return string
	 */
	private function cleanCookieValue(string $value): string
	{
		return \strtoupper(\sanitize_text_field(\wp_unslash($value)));
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
		$country = \array_filter(
			$this->getCountriesList(),
			static function ($item) use ($value) {
				$itemValue = $item['value'] ?? '';
				return $itemValue === $value;
			}
		);

		if (!$country) {
			return [];
		}

		$country = \reset($country);

		if (!isset($country['group'])) {
			return [];
		}

		return \array_flip($country['group']);
	}
}
