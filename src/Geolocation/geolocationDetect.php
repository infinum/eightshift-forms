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
	if (!Variables::getGeolocationUse()) {
		return;
	}

	// Require geo detect file from libs.
	require_once dirname(__DIR__, 2) . "{$sep}vendor{$sep}infinum{$sep}eightshift-libs{$sep}src{$sep}Geolocation{$sep}geolocationDetect.php";

	// Get cookie name.
	$cookieName = Variables::getGeolocationCookieName();

	// If the cookie exists, don't set it again.
	if (isset($_COOKIE[$cookieName])) {
		return;
	}

	// If expiration is not set use default of 1 day from current timestamp.
	$expires = Variables::getGeolocationExpiration();
	if (!$expires) {
		$expires = time() + DAY_IN_SECONDS;
	}

	try {
		$cookieValue = '';

		if (!Variables::getGeolocationUseCloudflare()) {
			// Detect geolocation from db and store it in the database.
			$cookieValue = getGeolocation(Variables::getGeolocationPharPath(), Variables::getGeolocationDbPath(), Variables::getGeolocationIp());
		} else {
			// Detect geolocation from Cloudflare header.
			$cookieValue = isset($_SERVER['HTTP_CF_IPCOUNTRY']) ? \strtoupper(\sanitize_text_field(\wp_unslash($_SERVER['HTTP_CF_IPCOUNTRY']))) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		// Set cookie if we have a value.
		if ($cookieValue) {
			setcookie(
				$cookieName,
				$cookieValue,
				$expires,
				'/'
			);
		}
	} catch (Exception $exception) {
		/*
		 * The getGeolocation will throw an error if the phar or geo db files are missing,
		 * but if we threw an exception here, that would break the execution of the WP app.
		 * This way we'll log the exception, but the site should work fine without setting
		 * the cookie.
		 */
		\error_log("Error code: {$exception->getCode()}, with message: {$exception->getMessage()}"); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
		return;
	}
}

// Activate function.
setEightshiftFormsLocationCookie();
