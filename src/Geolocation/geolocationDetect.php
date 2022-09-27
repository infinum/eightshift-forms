<?php

/**
 * File used in combination with WP-Rocket cache plugin to provide and set cookies.
 *
 * @package EightshiftLibs\Geolocation;
 */

declare(strict_types=1);

use EightshiftForms\Hooks\Variables;

/**
 * Set Eightshift forms geolocation cookie.
 *
 * @return void
 */
function setEightshiftFormsLocationCookie(): void {
	$sep = \DIRECTORY_SEPARATOR;

	require_once dirname(__DIR__, 2) . "{$sep}vendor{$sep}infinum{$sep}eightshift-libs{$sep}src{$sep}Geolocation{$sep}geolocationDetect.php";
	require_once dirname(__DIR__, 2) . "{$sep}src{$sep}Hooks{$sep}Variables.php";

	setLocationCookie(
		Variables::getGeolocationCookieName(),
		Variables::getGeolocationPharPath(),
		Variables::getGeolocationDbPath(),
		Variables::getGeolocationIp(),
		Variables::getGeolocationExpiration()
	);
}
