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

## Change the current locale
This filter can be used to change the value of current locale. By default WordPress sets the locale in the admin to `en_US` and with this filter it can be changed to whichever locale is needed (e.g. when using multilanguage plugin).

**Filter name:**
`es_forms_general_set_locale`

**Filter example:**
```php
// Set the custom locale.
add_filter('es_forms_general_set_locale', [$this, 'setFormsLocale']);

/**
 * Return the custom locale for forms.
 *
 * @param string $locale Default locale from WordPress
 * @return string
 */
public function setFormsLocale(string $locale): string
{
	// Get the custom locale (e.g. from WPML plugin)
	...
	return $locale;
}
```
