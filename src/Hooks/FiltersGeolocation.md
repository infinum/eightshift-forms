# Filters Geolocation
This document will provide you with the code examples for forms filters used in geolocation.

## Change default countries list.
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
