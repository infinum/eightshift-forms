<?php

/**
 * The Variables class, used for defining available variables.
 *
 * @package EightshiftForms\Hooks
 */

declare(strict_types=1);

namespace EightshiftForms\Hooks;

/**
 * The Variables class, used for defining available Variables.
 */
class Variables
{
	/**
	 * Get forms mode.
	 *
	 * @return bool
	 */
	public static function isDevelopMode(): bool
	{
		return defined('ES_DEVELOP_MODE') ? true : false;
	}

	/**
	 * Get forms to skip validation used for development of integration.
	 *
	 * @return bool
	 */
	public static function skipFormValidation(): bool
	{
		return defined('ES_DEVELOP_MODE_SKIP_VALIDATION') ? true : false;
	}

	/**
	 * Get forms to log out requests/responses.
	 *
	 * @return bool
	 */
	public static function isLogMode(): bool
	{
		return defined('ES_LOG_MODE') ? true : false;
	}

	/**
	 * Get API Key for HubSpot.
	 *
	 * @return string
	 */
	public static function getApiKeyHubspot(): string
	{
		return defined('ES_API_KEY_HUBSPOT') ? ES_API_KEY_HUBSPOT : '';
	}

	/**
	 * Get API Key for Greenhouse.
	 *
	 * @return string
	 */
	public static function getApiKeyGreenhouse(): string
	{
		return defined('ES_API_KEY_GREENHOUSE') ? ES_API_KEY_GREENHOUSE : '';
	}

	/**
	 * Get Board token Key for Greenhouse.
	 *
	 * @return string
	 */
	public static function getBoardTokenGreenhouse(): string
	{
		return defined('ES_BOARD_TOKEN_GREENHOUSE') ? ES_BOARD_TOKEN_GREENHOUSE : '';
	}

	/**
	 * Get API Key for Mailchimp.
	 *
	 * @return string
	 */
	public static function getApiKeyMailchimp(): string
	{
		return defined('ES_API_KEY_MAILCHIMP') ? ES_API_KEY_MAILCHIMP : '';
	}

	/**
	 * Get API Key for Mailerlite.
	 *
	 * @return string
	 */
	public static function getApiKeyMailerlite(): string
	{
		return defined('ES_API_KEY_MAILERLITE') ? ES_API_KEY_MAILERLITE : '';
	}

	/**
	 * Get API Key for Goodbits.
	 *
	 * @return string|array<string, mixed>
	 */
	public static function getApiKeyGoodbits()
	{
		return defined('ES_API_KEY_GOODBITS') ? ES_API_KEY_GOODBITS : '';
	}

	/**
	 * Get Google reCaptcha site key.
	 *
	 * @return string
	 */
	public static function getGoogleReCaptchaSiteKey()
	{
		return defined('ES_GOOGLE_RECAPTCHA_SITE_KEY') ? ES_GOOGLE_RECAPTCHA_SITE_KEY : '';
	}

	/**
	 * Get Google reCaptcha secret key.
	 *
	 * @return string
	 */
	public static function getGoogleReCaptchaSecretKey()
	{
		return defined('ES_GOOGLE_RECAPTCHA_SECRET_KEY') ? ES_GOOGLE_RECAPTCHA_SECRET_KEY : '';
	}

	/**
	 * Get forms geolocation country code.
	 *
	 * @return string
	 */
	public static function getGeolocation(): string
	{
		return defined('ES_GEOLOCAITON') ? strtoupper(ES_GEOLOCAITON) : '';
	}
}
