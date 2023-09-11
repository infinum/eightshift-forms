<?php

/**
 * Constants output class - defaults.
 *
 * @package EightshiftForms\Constants
 */

 declare(strict_types=1);

if (!defined('ES_GEOLOCATION_USE')) {
	define('ES_GEOLOCATION_USE', false);
}

if (!defined('ES_GEOLOCATION_USE_WP_ROCKET')) {
	define('ES_GEOLOCATION_USE_WP_ROCKET', false);
}

if (!defined('ES_GEOLOCATION_USE_CLOUDFLARE')) {
	define('ES_GEOLOCATION_USE_CLOUDFLARE', false);
}
