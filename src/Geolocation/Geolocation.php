<?php

/**
 * The file that is an Geolocation class.
 *
 * @package EightshiftForms\Geolocation;
 */

declare(strict_types=1);

namespace EightshiftForms\Geolocation;

use EightshiftForms\Hooks\Filters;
use EightshiftForms\Hooks\Variables;
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
	 * User location cache variable so we can optimize loading of db.
	 *
	 * @var string
	 */
	private $userLocation = '';

	/**
	 * Geolocation check if user is geolocated constant.
	 *
	 * @var string
	 */
	public const GEOLOCATION_IS_USER_LOCATED = 'es_geolocation_is_user_located';
	public const GEOLOCATION_COOKIE_NAME = 'es_geolocation_cookie_name';

	/**
	 * Register all the hooks
	 */
	public function register(): void
	{
		if (!\function_exists('is_plugin_active')) {
			include_once(\ABSPATH . 'wp-admin/includes/plugin.php');
		}

		if (!\is_plugin_active('wp-rocket/wp-rocket.php') && !Variables::getGeolocationUseWpRocketAdvancedCache()) {
			\add_filter('init', [$this, 'setLocationCookie']); // @phpstan-ignore-line
		}

		// WP Rocket specific hooks.
		\add_filter('rocket_advanced_cache_file', [$this, 'addNginxAdvanceCacheRules']);
		\add_filter('rocket_cache_dynamic_cookies', [$this, 'dynamicCookiesList']);

		\add_filter(self::GEOLOCATION_IS_USER_LOCATED, [$this, 'isUserGeolocated'], 10, 3);
	}

	/**
	 * Tooggle geolocation usage based on this flag.
	 *
	 * @return boolean
	 */
	public function useGeolocation(): bool
	{
		if (!\apply_filters(SettingsGeolocation::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false)) {
			return false;
		}

		return true;
	}

	/**
	 * List all dynamic cookies that will create new cached version.
	 *
	 * @param array<string, mixed> $items Items from the admin.
	 *
	 * @return array<int|string, mixed>
	 */
	public function dynamicCookiesList(array $items): array
	{
		if (!\apply_filters(SettingsGeolocation::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false) || !Variables::getGeolocationUseWpRocketAdvancedCache()) {
			return $items;
		}

		$items[] = $this->getGeolocationCookieName();

		return $items;
	}

	/**
	 * Add geolocation function in the advance-cache.php config file on plugin activation
	 *
	 * Used only with Nginx web servers.
	 *
	 * @param string $content Original file output.
	 */
	public function addNginxAdvanceCacheRules(string $content): string
	{
		if (!\apply_filters(SettingsGeolocation::FILTER_SETTINGS_GLOBAL_IS_VALID_NAME, false) || !Variables::getGeolocationUseWpRocketAdvancedCache()) {
			return $content;
		}

		$position = \strpos($content, '$rocket_config_class');

		// This part is string on purpose.
		$output = '
		$esFormsPath = ABSPATH . "wp-content/plugins/eightshift-forms/src/Geolocation/geolocationDetect.php";
		if (file_exists($esFormsPath)) {
			require_once $esFormsPath;
		};';

		$outputContent = \substr_replace($content, $output, $position, 0);

		// Override output with filter.
		$filterName = Filters::getFilterName(['geolocation', 'wpRocketAdvancedCache']);
		if (\has_filter($filterName)) {
			return \apply_filters($filterName, $content, $outputContent);
		}

		return $outputContent;
	}

	/**
	 * Get geolocation cookie name.
	 *
	 * @return string
	 */
	public function getGeolocationCookieName(): string
	{
		$name = Variables::getGeolocationCookieName();

		$filterName = Filters::getFilterName(['geolocation', 'cookieName']);
		if (\has_filter($filterName)) {
			$name = \apply_filters($filterName, null);
		}

		return $name;
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
		$path = Variables::getGeolocationPharPath();

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
		$path = Variables::getGeolocationDbPath();

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
		return Variables::getGeolocationExpiration();
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
		$filterName = Filters::getFilterName(['geolocation', 'disable']);
		if (\has_filter($filterName) && \apply_filters($filterName, null)) {
			return $formId;
		}

		// Returns user location retrieved from the API or cookie.
		// Used internal variable for caching optimisations.
		$userLocation = $this->userLocation;

		if (!$userLocation) {
			$userLocation = $this->getGeolocation();
		}

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
					$country = $this->getCountryGroup($location['value']);

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
