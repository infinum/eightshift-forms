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
	 * Get API Key for HubSpot.
	 *
	 * @return string
	 */
	public static function getApiKeyHubspot(): string
	{
		return \defined('ES_API_KEY_HUBSPOT') ? \ES_API_KEY_HUBSPOT : '';
	}

	/**
	 * Get API Key for Greenhouse.
	 *
	 * @return string
	 */
	public static function getApiKeyGreenhouse(): string
	{
		return \defined('ES_API_KEY_GREENHOUSE') ? \ES_API_KEY_GREENHOUSE : '';
	}

	/**
	 * Get Board token Key for Greenhouse.
	 *
	 * @return string
	 */
	public static function getBoardTokenGreenhouse(): string
	{
		return \defined('ES_BOARD_TOKEN_GREENHOUSE') ? \ES_BOARD_TOKEN_GREENHOUSE : '';
	}

	/**
	 * Get API Key for Mailchimp.
	 *
	 * @return string
	 */
	public static function getApiKeyMailchimp(): string
	{
		return \defined('ES_API_KEY_MAILCHIMP') ? \ES_API_KEY_MAILCHIMP : '';
	}

	/**
	 * Get API Key for Mailerlite.
	 *
	 * @return string
	 */
	public static function getApiKeyMailerlite(): string
	{
		return \defined('ES_API_KEY_MAILERLITE') ? \ES_API_KEY_MAILERLITE : '';
	}

	/**
	 * Get API Key for Goodbits.
	 *
	 * @return string|array<string, mixed>
	 */
	public static function getApiKeyGoodbits()
	{
		return \defined('ES_API_KEY_GOODBITS') ? \ES_API_KEY_GOODBITS : '';
	}

	/**
	 * Get Google reCaptcha site key.
	 *
	 * @return string
	 */
	public static function getGoogleReCaptchaSiteKey()
	{
		return \defined('ES_GOOGLE_RECAPTCHA_SITE_KEY') ? \ES_GOOGLE_RECAPTCHA_SITE_KEY : '';
	}

	/**
	 * Get Google reCaptcha secret key.
	 *
	 * @return string
	 */
	public static function getGoogleReCaptchaSecretKey()
	{
		return \defined('ES_GOOGLE_RECAPTCHA_SECRET_KEY') ? \ES_GOOGLE_RECAPTCHA_SECRET_KEY : '';
	}

	/**
	 * Get Google reCaptcha api key.
	 *
	 * @return string
	 */
	public static function getGoogleReCaptchaApiKey()
	{
		return \defined('ES_GOOGLE_RECAPTCHA_API_KEY') ? \ES_GOOGLE_RECAPTCHA_API_KEY : '';
	}

	/**
	 * Get Google reCaptcha project id key.
	 *
	 * @return string
	 */
	public static function getGoogleReCaptchaProjectIdKey()
	{
		return \defined('ES_GOOGLE_RECAPTCHA_PROJECT_ID_KEY') ? \ES_GOOGLE_RECAPTCHA_PROJECT_ID_KEY : '';
	}

	/**
	 * Get forms geolocation ip. Default: empty.
	 *
	 * @return string
	 */
	public static function getGeolocationIp(): string
	{
		return \defined('ES_GEOLOCATION_IP') ? \ES_GEOLOCATION_IP : '';
	}

	/**
	 * Get API Key for Clearbit.
	 *
	 * @return string|array<string, mixed>
	 */
	public static function getApiKeyClearbit()
	{
		return \defined('ES_API_KEY_CLEARBIT') ? \ES_API_KEY_CLEARBIT : '';
	}

	/**
	 * Get API key for ActiveCampaign.
	 *
	 * @return string
	 */
	public static function getApiKeyActiveCampaign(): string
	{
		return \defined('ES_API_KEY_ACTIVE_CAMPAIGN') ? \ES_API_KEY_ACTIVE_CAMPAIGN : '';
	}

	/**
	 * Get API URL for ActiveCampaign.
	 *
	 * @return string
	 */
	public static function getApiUrlActiveCampaign(): string
	{
		return \defined('ES_API_URL_ACTIVE_CAMPAIGN') ? \ES_API_URL_ACTIVE_CAMPAIGN : '';
	}

	/**
	 * Get API key for Airtable.
	 *
	 * @return string
	 */
	public static function getApiKeyAirtable(): string
	{
		return \defined('ES_API_KEY_AIRTABLE') ? \ES_API_KEY_AIRTABLE : '';
	}

	/**
	 * Get API URL for Moments.
	 *
	 * @return string
	 */
	public static function getApiUrlMoments(): string
	{
		return \defined('ES_API_URL_MOMENTS') ? \ES_API_URL_MOMENTS : '';
	}

	/**
	 * Get API Key for Moments.
	 *
	 * @return string
	 */
	public static function getApiKeyMoments(): string
	{
		return \defined('ES_API_KEY_MOMENTS') ? \ES_API_KEY_MOMENTS : '';
	}

	/**
	 * Get API Key for Workable.
	 *
	 * @return string
	 */
	public static function getApiKeyWorkable(): string
	{
		return \defined('ES_API_KEY_WORKABLE') ? \ES_API_KEY_WORKABLE : '';
	}

	/**
	 * Get Board token Key for Workable.
	 *
	 * @return string
	 */
	public static function getSubdomainWorkable(): string
	{
		return \defined('ES_SUBDOMAIN_WORKABLE') ? \ES_SUBDOMAIN_WORKABLE : '';
	}

	/**
	 * Get API Key for Jira.
	 *
	 * @return string|array<string, mixed>
	 */
	public static function getApiKeyJira()
	{
		return \defined('ES_API_KEY_JIRA') ? \ES_API_KEY_JIRA : '';
	}

	/**
	 * Get API Board for Jira.
	 *
	 * @return string|array<string, mixed>
	 */
	public static function getApiBoardJira()
	{
		return \defined('ES_API_BOARD_JIRA') ? \ES_API_BOARD_JIRA : '';
	}

	/**
	 * Get API User for Jira.
	 *
	 * @return string|array<string, mixed>
	 */
	public static function getApiUserJira()
	{
		return \defined('ES_API_USER_JIRA') ? \ES_API_USER_JIRA : '';
	}
}
