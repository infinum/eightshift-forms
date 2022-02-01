# Variables

This document will provide you with the code examples for forms global variables that you usually put in the `wp-config.php` file.

## Set forms to develop mode

This variable will set forms to develop mode that will do the following actions:
* disable value removal after the form is successfully submitted. 

```php
define('ES_DEVELOP_MODE', true);
```

## Set forms to skip validation.

This variable will set forms to skip validation when submitting. This is useful when adding a new integration or testing API responses.

```php
define('ES_DEVELOP_MODE_SKIP_VALIDATION', true);
```

## Set forms to output log.

This variable will set forms to output log file for all requests and responses. This is useful when adding a new integration or testing API responses.

```php
define('ES_LOG_MODE', true);
```

## Set Hubspot api key

This variable will set forms Hubspot api key and you will not be able to change it from the admin.

```php
define('ES_API_KEY_HUBSPOT', '<api-key>');
```

## Set Greenhouse API key

This variable will set forms Greenhouse api key and you will not be able to change it from the admin.

```php
define('ES_API_KEY_GREENHOUSE', '<api-key>');
```

## Set Greenhouse board token

This variable will set forms Greenhouse board token and you will not be able to change it from the admin.

```php
define('ES_BOARD_TOKEN_GREENHOUSE', '<board-token>');
```

## Set Mailchimp api key

This variable will set forms Mailchimp api key and you will not be able to change it from the admin.

```php
define('ES_API_KEY_MAILCHIMP', '<api-key>');
```

## Set Mailerlite api key

This variable will set forms Mailerlite api key and you will not be able to change it from the admin.

```php
define('ES_API_KEY_MAILERLITE', '<api-key>');
```

## Set Goodbits api key

This variable will set forms Goodbits api key and you will not be able to change it from the admin.

This key can be string as one api key or json array for multiple keys that will be showed in the list selector.

string:
```php
define('ES_API_KEY_GOODBITS', '<api-key>');
```

string:
```php
define('ES_API_KEY_GOODBITS', "{'Android':'<api-key>','Frontend':'<api-key>'}");
```

## Set Google reCaptcha

This variables will set Google reCaptcha site and secret keys and you will not be able to change it from the admin.

This two keys must be used together!!!

string:
```php
define('ES_GOOGLE_RECAPTCHA_SITE_KEY', '<site-key>');
define('ES_GOOGLE_RECAPTCHA_SECRET_KEY', '<secret-key>');
```
