# Variables

This document will provide you with the code examples for forms global variables that you usually put in the `wp-config.php` file.

## Set forms to develop mode

This variable will set forms to develop mode that will do the following actions:
* disable value removal after the form is successfully submitted. 

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
