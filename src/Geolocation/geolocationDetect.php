<?php // phpcs:ignore PSR1.Files.SideEffects.FoundWithSymbols

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
function setEightshiftFormsLocationCookie(): void
{
	$sep = DIRECTORY_SEPARATOR;

	// Require forms hooks.
	require_once dirname(__DIR__, 2) . "{$sep}src{$sep}Hooks{$sep}Variables.php";

	// Bailout if geolocation is not used.
	if (!Variables::getGeolocationUse() || !Variables::getGeolocationUseWpRocketAdvancedCache()) {
		return;
	}

	// Require geo detect file from libs.
	require_once dirname(__DIR__, 2) . "{$sep}vendor{$sep}infinum{$sep}eightshift-libs{$sep}src{$sep}Geolocation{$sep}geolocationDetect.php";

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
