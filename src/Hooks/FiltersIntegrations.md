# Filters Integrations
This document will provide you with the code examples for forms filters used in integrations.

## Change form fields data before output.
This filter is used if you want to change form fields data before output. By changing the name of the filter you will target different integrations.

**Filter name:**
`es_forms_integration_<integration_name>_data`

**Filter example for Greenhouse:**
```php
// Change Greenhouse integration form data.
add_filter('es_forms_integration_greenhouse_data', [$this, 'getIntegrationGreenhouseData'], 10, 2);

/**
 * Change Greenhouse integration form data.
 *
 * @param array<string, mixed> $data Array of component/attributes data.
 * @param string $fromId Form Id.
 *
 * @return array<string, mixed>
 */
public function getIntegrationGreenhouseData(array $data, string $formId): array
{
	return $data;
}
```

## Set default values in integration settings for form fields.
This filter is used if you want to provide default values in each integration settings for form fields. By changing the name of the filter you will target different integrations. This filter will provide default values until changes are stored in the database.

**Filter name:**
`es_forms_integration_<integration_name>_fields_settings`

**Available keys:**
* **desktop** - Breakpoint.
	* 1 - 12
	* integer
* **large** - Breakpoint.
	* 1 - 12
	* integer
* **tablet** - Breakpoint.
	* 1 - 12
	* integer
* **mobile** - Breakpoint.
	* 1 - 12
	* integer
* **use** - Show/hide field.
	* true
	* boolean
* **order** - Order number of the field, it depends on the number of fields.
	* integer
* **file-info-label** - Label used in the file field.
	* string
* **label** - Label used to change the default field label.
	* string
* **field-style** - Key provided by the field style filter.
	* string

**Filter example for Greenhouse:**
```php
// Change Greenhouse integration fields settings data.
add_filter('es_forms_integration_greenhouse_fields_settings', [$this, 'getIntegrationGreenhouseFieldsSettings']);

/**
 * Change Greenhouse integration fields settings data.
 *
 * @param array<string, mixed> $fields Array of fields and values.
 * @param string $fromId Form Id.
 *
 * @return array<int|string, array<string, bool|int|string>>
 */
public function getIntegrationGreenhouseFieldsSettings(array $fields, string $formId): array
{
	return [
		'first_name' => [
			'desktop' => 4,
			'use' => false,
			'order' => 5,
			'label' => 'New label',
		],
		'last_name' => [
			'large' => 6,
		],
	];
}
```

## Disable editing of integration fields settings.
This filter is used if you want to disable edit option in each integration settings for fields. By changing the name of the filter you will target different integrations. If this filter is used in combination with the previous filter it will ignore all all-ready stored settings in the database.

**Filter name:**
`es_forms_integration_<integration_name>_fields_settings_is_editable`

**Filter example for Greenhouse:**
```php
// Disable editing of Greenhouse integration fields settings.
add_filter('es_forms_integration_greenhouse_fields_settings_is_editable', [$this, 'disableEditIntegrationGreenhouseFieldsSettings']);

/**
 * Disable editing of Greenhouse integration fields settings.
 *
 * @return bool
 */
public function disableEditIntegrationGreenhouseFieldsSettings(): bool
{
	return false;
}
```

## Add custom text in admin settings before field settings.
This filter is used to add custom text in the before content field element in the admin settings fields settings section.

**Filter name:**
`es_forms_integration_<integration_name>_admin_field_settings_additional_content`

**Filter example for Greenhouse:**
```php
// Set Greenhouse integration additional content for fields settings in admin.
add_filter('es_forms_integration_greenhouse_admin_field_settings_additional_content', [$this, 'getAdminFieldSettingsAdditionalContentGreenhouse']);

/**
 * Set Greenhouse integration additional content for fields settings in admin.
 *
 * @return string
 */
public function getAdminFieldSettingsAdditionalContentGreenhouse(): string
{
	return "
		<p>Insert custom string here.</p>
	";
}
```

## Change Hubspot file upload options.
This filter is used to change default file upload options set by forms and Hubspot. We use this [api](https://legacydocs.hubspot.com/docs/methods/files/v3/upload_new_file), and you can change any of these options.

**Filter name:**
`es_forms_integration_hubspot_files_options`

**Filter example:**
```php
// Set Hubspot file upload custom options.
add_filter('es_forms_integration_hubspot_files_options', [$this, 'getHubspotFileUploadCustomOptions']);

/**
 * Set Hubspot file upload custom options.
 *
 * @return array<mixed>
 */
public function getHubspotFileUploadCustomOptions(): string
{
	return [
			'folderPath' => '/esforms',
			'options' => wp_json_encode([
				"access" => "PUBLIC_NOT_INDEXABLE",
				"overwrite" => false,
			]),
		];
}
```

## Change Clearbit integration fields map.
This filter provides you the ability to change how we map Clearbit fields so you can combine multiple fields in one add some new one.

**Filter name:**
`es_forms_integration_clearbit_map`

**Filter example:**
```php
// Change Clearbit fields map.
add_filter('es_forms_integration_clearbit_map', [$this, 'getClearbitFieldsMap']);

/**
 * Change Clearbit fields map.
 *
 * @param array $params Default params provided by the forms.
 *
 * @return array<mixed>
 */
public function getClearbitFieldsMap(array $params): array
{
	$street = $params['company-street-number'] ?? '';
	$city = $params['company-city'] ?? '';
	$postalCode = $params['company-postal-code'] ?? '';

	$params['company-location-combined'] = "{$street} {$city} {$postalCode}";

	return $params;
}
```
