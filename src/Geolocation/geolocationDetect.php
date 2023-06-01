<?php // phpcs:ignore PSR1.Files.SideEffects.FoundWithSymbols

/**
 * File used in combination with WP-Rocket cache plugin to provide and set cookies.
 *
 * @package EightshiftLibs\Geolocation;
 */

declare(strict_types=1);

use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Variables;

/**
 * Set Eightshift forms geolocation cookie.
 *
 * @return void
 */
function setEightshiftFormsLocationCookie(): void
{
	// Require forms hooks.
	require_once Helper::getRealpath(dirname(__DIR__, 2) . "/src/Hooks/Variables.php");

	// Bailout if geolocation is not used.
	if (!Variables::getGeolocationUse() || !Variables::getGeolocationUseWpRocketAdvancedCache()) {
		return;
	}

	// Require geo detect file from libs.
	require_once Helper::getRealpath(dirname(__DIR__, 2) . "/vendor/infinum/eightshift-libs/src/Geolocation/geolocationDetect.php");

	// Run setting of cookie.
	setLocationCookie( // @phpstan-ignore-line
		Variables::getGeolocationCookieName(),
		Variables::getGeolocationPharPath(),
		Variables::getGeolocationDbPath(),
		Variables::getGeolocationIp(),
		Variables::getGeolocationExpiration()
	);
}

// Activate function.
setEightshiftFormsLocationCookie();
