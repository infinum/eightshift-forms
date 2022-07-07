# Filters General
This document will provide you with the code examples for forms filters used in general.

## Change http request arguments
This filter can be used to change CURL timeout for the file upload if you have to upload large files.

**Filter name:**
`es_forms_general_http_request_args`

**Filter example:**
```php
// Return http request args.
add_filter('es_forms_general_http_request_args', [$this, 'getHttpRequestArgs']);

/**
 * Return http request args.
 *
 * @param array<int, mixed> $args Arguments from core.
 *
 * @return array<int, mixed>
 */
public function getHttpRequestArgs(array $args): array
{
	$args['timeout'] = 50;

	return $args;
}
```
