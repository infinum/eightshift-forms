# GeoLocation with WP Rocket cache

When geolocation is used in combination with WP-Rocket plugin it will not work by default because the cookie is set after the page is loaded and at that point it is too late to provide content.

WP-Rocket plugin provides options/hooks to fix this. By using hook `rocket_advanced_cache_file` we are able to inject our custom function in the page generation process inside the `wp-content/advanced-cache.php` file. By modifying this file we can detect users geolocation from the IP and set the cookie manually before the page is loaded. This way WP-Rocket can detect that cookie and provide the necessary cache. Below is the process of setting it:

For this to use you need to make a few changes.

> Make sure all of these constants are set and configured **before** activating the WP-Rocket plugin. If you change anything in these constants you will need to **deactivate and activate** the WP-Rocket plugin!

## Set global variables in wp-config.php

We don't like to duplicate code and configuration so if you want to use geolocation with WP-Rocket cache plugin you need to put a few geolocation global variables in you `wp-config.php` file.

### ES_GEOLOCATION_USE

This constant will activate geolocation and you will not be able to disable it from the setting. This is used to make sure the geolocation is active during the WP-Rocket plugin activation process.

```php
define('ES_GEOLOCATION_USE', true);
```

### ES_GEOLOCATION_USE_WP_ROCKET_ADVANCED_CACHE

This constant is used so that the advanced cache settings can work correctly with the WP-Rocket plugin.

```php
define('ES_GEOLOCATION_USE_WP_ROCKET_ADVANCED_CACHE', true);
```

## What will happen when activating WP-Rocket plugins?

When activating the WP-Rocket plugin we will set our geolocation cookie to the WP-Rocket dynamic cookie list and include our custom geolocation function in the `advanced-cache.php` file that will set the necessary cookie before the WP-Rocket detects the correct cache file.

We have some additional global constants that you can use with this feature. All details you can read [here](./../Hooks/Variables.md).
