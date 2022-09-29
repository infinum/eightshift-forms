# Filters Geolocation
This document will provide you with the code examples for forms filters used in geolocation.

> If the filter has global variable equivalent and the constant is active, filter will not be used.

## Change default countries list
This filter provides you with the ability to add/remove/edit countries list and countries groups.

**Filter name:**
`es_forms_geolocation_countries_list`

**Filter example:**
```php
// Change the default geolocation countries list.
add_filter('es_forms_geolocation_countries_list', [$this, 'getGeolocationCountriesList']);

/**
 * Change the default geolocation countries list.
 *
 * @param array<mixed> $countries Countries list from internal db.
 *
 * @return array<mixed>
 */
public function getGeolocationCountriesList(array $countries): array
{
	return \array_merge(
		$countries,
		[
			[
				'label' => \__('<country-name>', 'text-domain'),
				'value' => '<country-value>',
				'group' => [
					'<country-value>',
				],
			],
		],
	);
}
```

## Disable geolocation
This filter provides you with the ability to totally disable geolocation on the frontend usage.
Generally used for GDPR and other purposes.

**Global variable alternative:**
`ES_GEOLOCATION_USE`

**Filter name:**
`es_forms_geolocation_disable`

**Filter example:**
```php
// Disable geolocation.
add_filter('es_forms_geolocation_disable', [$this, 'getGeolocationDisable']);

/**
 * Disable geolocation.
 *
 * @return boolean
 */
public function getGeolocationDisable(): bool
{
	return true;
}
```

## Provide custom geolocation db location.
This filter provides you with the ability to provide custom database location for geolocation.

**Global variable alternative:**
`ES_GEOLOCATION_DB_PATH`

**Filter name:**
`es_forms_geolocation_db_location`

**Filter example:**
```php
// Geolocation db location.
add_filter('es_forms_geolocation_db_location', [$this, 'getGeolocationDbLocation']);

/**
 * Geolocation db location.
 *
 * @return string
 */
public function getGeolocationDbLocation(): string
{
	return __DIR__ . \DIRECTORY_SEPARATOR . 'geoip.mmdb';
}
```

## Provide custom geolocation phar location.
This filter provides you with the ability to provide custom database location for geolocation.

**Global variable alternative:**
`ES_GEOLOCATION_PHAR_PATH`

**Filter name:**
`es_forms_geolocation_phar_location`

**Filter example:**
```php
// Geolocation phar location.
add_filter('es_forms_geolocation_phar_location', [$this, 'getGeolocationPharLocation']);

/**
 * Geolocation phar location.
 *
 * @return string
 */
public function getGeolocationPharLocation(): string
{
	return __DIR__ . \DIRECTORY_SEPARATOR . 'geoip.phar';
}
```

## Provide custom geolocation cookie name.
This filter enables providing custom cookie name for geolocation.

**Global variable alternative:**
`ES_GEOLOCATION_COOKIE_NAME`

**Filter name:**
`es_forms_geolocation_cookie_name`

**Filter example:**
```php
// Geolocation cookie name.
add_filter('es_forms_geolocation_cookie_name', [$this, 'getGeolocationCookieName']);

/**
 * Geolocation cookie name.
 *
 * @return string
 */
public function getGeolocationCookieName(): string
{
	return 'esForms-country';
}
```

## Provide custom WP-Rocket advanced-cache.php function.
This filter enables providing custom function in WP-Rocket plugin activation process.

**Filter name:**
`es_forms_geolocation_wp_rocket_advanced_cache`

**Filter example:**
```php
// Geolocation WP-Rocket advanced cache.
add_filter('es_forms_geolocation_wp_rocket_advanced_cache', [$this, 'getGeolocationWpRocketAdvancedCache']);

/**
 * Geolocation WP-Rocket advanced cache.
 *
 * @param string $content Original WP-Rocket output content.
 * @param string $outputContent Default forms output content.
 *
 * @return string
 */
public function getGeolocationWpRocketAdvancedCache(string $content, string $outputContent): string
{
	$position = \strpos($content, '$rocket_config_class');

	$output = '
		$esFormsPath = ABSPATH . "wp-content/plugins/eightshift-forms/src/Geolocation/geolocationDetect.php";
		if (file_exists($esFormsPath)) {
			require_once $esFormsPath;
		};';

	return \substr_replace($content, $output, $position, 0);
}
```
