# Filters Enrichment
This document will provide you with the code examples for forms filters used in tracking.

## Map localStorage tags to Hubspot field
This filter provides you with the ability to map you localStorage tags got from the url GET parameters and map them to the request fields sent to the Hubspot api.

**Filter name:**
`es_forms_integration_hubspot_local_storage_map`

**Filter example:**
```php
// Map Hubspot fields with custom tags from localStorage.
\add_filter('es_forms_integration_hubspot_local_storage_map', [$this, 'getIntegrationHubspotLocalStorageMap'], 10, 4);

/**
 * Map Hubspot fields with custom tags from localStorage.
 *
 * @param array<int, mixed> $params Params from Hubspot integration prepared for output.
 * @param array<int, mixed> $storage Data form storage.
 * @param array<int, mixed> $originalParams Original params with all custom fields.
 * @param array<int, string> $enrichmentConfig Enrichment config passed to JavaScript.
 *
 * @return array<int, mixed>
 */
public function getIntegrationHubspotLocalStorageMap(array $params, array $storage, array $originalParams, array $enrichmentConfig): array
{
	if (!$storage) {
		return $params;
	}

	// Additional tags to allow.
	$allowedTags = $enrichmentConfig['allowed'] ?? [];

	if (!$allowedTags) {
		return $params;
	}

	$allowedTags = array_flip($allowedTags);

	// Filter allowed storage params.
	$storage = \array_filter(
		$storage,
		static function ($key) use ($allowedTags) {
			if (isset($allowedTags[$key])) {
				return true;
			}
		},
		\ARRAY_FILTER_USE_KEY
	);

	// If storage is empty after the filter bailout.
	if (!$storage) {
		return $params;
	}

	// Loop storage and append data to the params with the correct dataset.
	foreach ($storage as $key => $param) {
		if (!isset($originalParams[$key])) {
			continue;
		}

		$name = $originalParams[$key]['name'] ?? '';

		if (!$name) {
			continue;
		}

		$params[] = [
			'name' => $name,
			'value' => $param,
			'objectTypeId' => $originalParams[$key]['objectTypeId'] ?? '',
		];
	}

	return $params;
}
```
