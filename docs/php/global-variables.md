# WordPress Global variables

This document will provide you with the code examples for forms global variables that you usually put in the `wp-config.php` file.

## Set Hubspot api key

This variable will set forms Hubspot api key and you will not be able to change it from the admin.

Type:
* string

Default:
* empty

```php
define('ES_API_KEY_HUBSPOT', '<api-key>');
```

## Set Greenhouse API key

This variable will set forms Greenhouse api key and you will not be able to change it from the admin.

Type:
* string

Default:
* empty

```php
define('ES_API_KEY_GREENHOUSE', '<api-key>');
```

## Set Greenhouse board token

This variable will set forms Greenhouse board token and you will not be able to change it from the admin.

Type:
* string

Default:
* empty

```php
define('ES_BOARD_TOKEN_GREENHOUSE', '<board-token>');
```

## Set Mailchimp api key

This variable will set forms Mailchimp api key and you will not be able to change it from the admin.

Type:
* string

Default:
* empty

```php
define('ES_API_KEY_MAILCHIMP', '<api-key>');
```

## Set Mailerlite api key

This variable will set forms Mailerlite api key and you will not be able to change it from the admin.

Type:
* string

Default:
* empty

```php
define('ES_API_KEY_MAILERLITE', '<api-key>');
```

## Set Goodbits api key

This variable will set forms Goodbits api key and you will not be able to change it from the admin.

This key can be string as one api key or json array for multiple keys that will be showed in the list selector.

Type:
* string

Default:
* empty

```php
define('ES_API_KEY_GOODBITS', '<api-key>');
```

Type:
* json-string

Default:
* empty

```php
define('ES_API_KEY_GOODBITS', "{'Android':'<api-key>','Frontend':'<api-key>'}");
```

## Set Google reCaptcha

These constants will set Google reCaptcha site and secret keys. You cannot change the from the admin interface.

You **must** add both constants in order to use Google reCaptcha.

Type:
* string

Default:
* empty

```php
define('ES_GOOGLE_RECAPTCHA_SITE_KEY', '<site-key>');
define('ES_GOOGLE_RECAPTCHA_SECRET_KEY', '<secret-key>');
```

## Set geolocation use

This constant will manually set geolocation usage to active. While this constant is active you will not be able to deactivate it from settings.

Type:
* boolean

Default:
* false

```php
define('ES_GEOLOCATION_USE', true);
```

## Set geolocation WP-Rocket advanced cache

This constant will activate WP-Rocket advanced cache filter to create custom geolocation function in advanced-cache.php file.

Type:
* boolean

Default:
* false

```php
define('ES_GEOLOCATION_USE_WP_ROCKET_ADVANCED_CACHE', true);
```

## Set geolocation IP

This constant will manually set geolocation IP address to a fixed value and the geolocation will always return the same location.

Type:
* string

Default:
* empty

```php
define('ES_GEOLOCATION_IP', '<ip>');
```

## Set geolocation cookie name

This constant will define the cookie name used for geolocation. Generally, you will never need to change this value.

Type:
* string

Default:
* esForms-country

```php
define('ES_GEOLOCATION_COOKIE_NAME', '<cookie-name>');
```

## Set geolocation Phar location

This constant will define geolocation phar location. With this constant, you can provide your custom project's geolocation phar file. You will use this if you want to control the phar file version and keep it better up to date.

Type:
* string

Default:
* absolute path the to the Eightshift-form geolocation .phar file.

```php
define('ES_GEOLOCATION_PHAR_PATH', '<absolute-path>');
```

## Set geolocation Db location

This constant will define geolocation db location. With this constant, you can provide your custom project's geolocation db file. You will use this if you want to control the db file version and keep it better up to date.

Type:
* string

Default:
* absolute path the to the Eightshift-form geolocation .mmdb file.

```php
define('ES_GEOLOCATION_DB_PATH', '<absolute-path>');
```

## Set geolocation cookie expiration time

This constant will define geolocation cookie expiration time.

Type:
* integer

Default:
* 15 days in timestamp.

```php
define('ES_GEOLOCATION_COOKIE_EXPIRATION', '<time>');
```

## Set Clearbit api key

This variable will set forms Clearbit api key and you will not be able to change it from the admin.

Type:
* string

Default:
* empty

```php
define('ES_API_KEY_CLEARBIT', '<api-key>');
```
## Set the ActiveCampaign API key

This variable will set form's ActiveCampaign API key. You will not be able to change it from the admin.

Type:
* string

Default:
* empty

```php
define('ES_API_KEY_ACTIVE_CAMPAIGN', '<api-key>');
```

## Set the ActiveCampaign API URL

This variable will set form's ActiveCampaign API URL. You will not be able to change it from the admin.

Type:
* string

Default:
* empty

```php
define('ES_API_URL_ACTIVE_CAMPAIGN', '<api-url>');
```

## Set the Airtable API key

This variable will set form's Airtable API key. You will not be able to change it from the admin.

Type:
* string

Default:
* empty

```php
define('ES_API_KEY_AIRTABLE', '<api-key>');
```

## Set the Airtable API username

This variable will set form's Airtable API username. You will not be able to change it from the admin.

Type:
* string

Default:
* empty

```php
define('ES_API_USERNAME_AIRTABLE', '<api-username>');
```

## Set the Airtable API password

This variable will set form's Airtable API password. You will not be able to change it from the admin.

Type:
* string

Default:
* empty

```php
define('ES_API_PASSWORD_AIRTABLE', '<api-password>');
```
