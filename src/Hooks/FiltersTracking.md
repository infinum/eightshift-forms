# Filters Tracking
This document will provide you with the code examples for forms filters used in tracking.

## Add additional tags
This filter provides you with the ability to add/remove/edit url tags sent to the backend when processing the form request.
You can use this tags list to map query parameters to you fields.

**Filter name:**
`es_forms_tracking_allowed_tags`

**Filter example:**
```php
// Add additional tracking tags to check.
\add_filter('es_forms_tracking_allowed_tags', [$this, 'getTrackingAllowedTags']);

	/**
 * Add additional tracking tags to check.
 *
 * @param array<int, string> $tags Default allowed tags.
 *
 * @return array<int, string>
 */
public function getTrackingAllowedTags(array $tags): array
{
	return [
		...$tags,
		'utm_source',
		'utm_content',
		'utm_campaign',
	];
}
```

## Map local storage tags to Hubspot field
This filter provides you with the ability to map you local storage tags got from the url GET parameters and map them to the request fields sent to the Hubspot api.

**Filter name:**
`es_forms_integration_hubspot_local_storage_map`

**Filter example:**
```php
// Map Hubspot fields with custom tags from local storage.
\add_filter('es_forms_integration_hubspot_local_storage_map', [$this, 'getIntegrationHubspotLocalStorageMap'], 10, 2);

/**
 * Map Hubspot fields with custom tags from local storage.
 *
 * @param array<int, mixed> $params Params from Hubspot integration.
 * @param array<int, mixed> $storage Data form storage.
 *
 * @return array<int, mixed>
 */
public function getIntegrationHubspotLocalStorageMap(array $params, array $storage): array
{
	if (!$storage) {
		return $params;
	}

	foreach ($params as $key => $param) {
		$name = $param['name'] ?? '';

		if (!$name) {
			continue;
		}

		if ($name === 'utm_source' || $name === 'utm_content' || $name === 'utm_campaign') {
			if (isset($storage[$name])) {
				$params[$key]['value'] = $storage[$name];
			}
		}
	}

	return $params;
}
```

## Change amount of days localStorage is storing the data.
We store localStorage for 30 days by default, with this filter you can change this value.

**Filter name:**
`es_forms_tracking_expiration`

**Filter example:**
```php
// Get tracking expiration days.
\add_filter('es_forms_tracking_expiration', [$this, 'getTrackingExpiration']);

/**
 * Get tracking expiration days.
 *
 * @return string
 */
public function getTrackingExpiration(): string
{
	return '15';
}
```
