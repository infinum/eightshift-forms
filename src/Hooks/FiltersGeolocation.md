# Filters Geolocation
This document will provide you with the code examples for forms filters used in geolocation.

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
