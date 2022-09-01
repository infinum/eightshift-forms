# Filters General
This document will provide you with the code examples for forms filters used in general.

## Change http request timeout
This filter can be used to change CURL timeout for the file upload if you have to upload large files.

**Filter name:**
`es_forms_general_http_request_timeout`

**Filter example:**
```php
// Return http request timeout.
add_filter('es_forms_general_http_request_timeout', [$this, 'getHttpRequestTimeout']);

/**
 * Return http request timeout.
 *
 * @return int
 */
public function getHttpRequestTimeout(): int
{
	return 50;
}
```
