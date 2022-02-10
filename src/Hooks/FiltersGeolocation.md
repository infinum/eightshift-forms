# Filters Geolocation
This document will provide you with the code examples for forms filters used in geolocation.

## Change default countries list
This filter provides you the ability to add/remove/edit countries list and countries groups.

**Filter name:**
`es_forms_geolocation_countries_list`

**Filter example:**
```php
// Change the default geolocation countries list.
add_filter('es_forms_geolocation_countries_list', [$this, 'getGeolocationCountriesList']);

/**
 * Change the default geolocation countries list.
 *
 * @param array<string> $countries Countries list from internal db.
 *
 * @return array<string>
 */
public function getGeolocationCountriesList(array $countries): array
{
	return array_merge(
		$countries,
		[
			[
				'label' => __('<country-name>', 'text-domain'),
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
This filter provides you the ability to totally disable geolocation on the frontend usage.
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

## Provide custom user location
This filter provides you the ability to manually provide user location from your own database or API.

**Filter name:**
`es_forms_geolocation_user_location`

**Filter example:**
```php
// Get user location country code.
add_filter('es_forms_geolocation_user_location', [$this, 'getUserLocation']);

/**
 * Get user location country code.
 *
 * @return string
 */
public function getUserLocation(): string
{
	return 'HR';
}
```
