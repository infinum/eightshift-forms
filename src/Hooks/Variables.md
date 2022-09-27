# Variables

This document will provide you with the code examples for forms global variables that you usually put in the `wp-config.php` file.

## Set forms to develop mode

This variable will set forms to develop mode that will do the following actions:
* output new global settings for testing inputs

```php
define('ES_DEVELOP_MODE', true);
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

These constants will set Google reCaptcha site and secret keys. You cannot change the from the admin interface.

You **must** add both constants in order to use Google reCaptcha.

string:
```php
define('ES_GOOGLE_RECAPTCHA_SITE_KEY', '<site-key>');
define('ES_GOOGLE_RECAPTCHA_SECRET_KEY', '<secret-key>');
```

## Set geolocation IP

This constant will manually set geolocation IP address and will skip cookie set or any caching.

string:
```php
define('ES_GEOLOCATION_IP', '<ip>');
```

## Set geolocation cookie name

This constant will define cookie name used for geolocation.

string:
```php
define('ES_GEOLOCATION_COOKIE_NAME', 'esForms-country');
```

## Set geolocation Phar location

This constant will define geolocation phar location.

string:
```php
define('ES_GEOLOCATION_PHAR_PATH', '<absolute-path>');
```

## Set geolocation Db location

This constant will define geolocation db location.

string:
```php
define('ES_GEOLOCATION_DB_PATH', '<absolute-path>');
```

## Set geolocation cookie expiration time

This constant will define geolocation cookie expiration time.

string:
```php
define('ES_GEOLOCATION_COOKIE_EXPIRATION', '<time>');
```

## Set Clearbit api key

This variable will set forms Clearbit api key and you will not be able to change it from the admin.

```php
define('ES_API_KEY_CLEARBIT', '<api-key>');
```
## Set the ActiveCampaign API key

This variable will set form's ActiveCampaign API key. You will not be able to change it from the admin.

```php
define('ES_API_KEY_ACTIVE_CAMPAIGN', '<api-key>');
```

## Set the ActiveCampaign API URL

This variable will set form's ActiveCampaign API URL. You will not be able to change it from the admin.

```php
define('ES_API_URL_ACTIVE_CAMPAIGN', '<api-url>');
```
