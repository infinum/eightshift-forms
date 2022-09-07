# Filters General
This document will provide you with the code examples for forms filters used in general.

## Change http request timeout
This filter can be used to change the cURL timeout for the file upload, useful if you have to upload large files.

**Filter name:**
`es_forms_general_http_request_timeout`

**Filter example:**
```php
// Return the HTTP request timeout.
add_filter('es_forms_general_http_request_timeout', [$this, 'getHttpRequestTimeout']);

/**
 * Return the HTTP request timeout.
 *
 * @return int
 */
public function getHttpRequestTimeout(): int
{
	return 50;
}
```
