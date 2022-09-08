# Filters Troubleshooting
This document will provide you with the code examples for forms filters used in troubleshooting.

## Output debug logs to external source
This filter provides you with the ability to output internal debug log to an external source.

**Filter name:**
`es_forms_troubleshooting_output_log`

**Filter example:**
```php
// Change the default output log location.
add_filter('es_forms_troubleshooting_output_log', [$this, 'getGeolocationCountriesList']);

/**
 * Change the default output log location.
 *
 * @param array<mixed> $data Data to output.
 *
 * @return bool
 */
public function getGeolocationCountriesList(array $data): bool
{
	// Do your magic here.

	return true;
}
```
