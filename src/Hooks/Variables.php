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
	 * Get API Key for Talentlyft.
	 *
	 * @return string
	 */
	public static function getApiKeyTalentlyft(): string
	{
		return \defined('ES_API_KEY_TALENTLYFT') ? \ES_API_KEY_TALENTLYFT : '';
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

	/**
	 * Get API Key for Pipedrive.
	 *
	 * @return string|array<string, mixed>
	 */
	public static function getApiKeyPipedrive()
	{
		return \defined('ES_API_KEY_PIPEDRIVE') ? \ES_API_KEY_PIPEDRIVE : '';
	}

	/**
	 * Get API Key for Corvus.
	 *
	 * @param string $storeId Store ID.
	 *
	 * @return string
	 */
	public static function getApiKeyCorvus(string $storeId): string
	{
		$key = "ES_API_KEY_CORVUS_{$storeId}";
		return \defined($key) ? \constant($key) : '';
	}

	/**
	 * Get API Key for Paycek.
	 *
	 * @return string
	 */
	public static function getApiKeyPaycek(): string
	{
		return \defined('ES_API_KEY_PAYCEK') ? \ES_API_KEY_PAYCEK : '';
	}

	/**
	 * Get API Profile Key for Paycek.
	 *
	 * @return string
	 */
	public static function getApiProfileKeyPaycek(): string
	{
		return \defined('ES_PROFILE_KEY_PAYCEK') ? \ES_PROFILE_KEY_PAYCEK : '';
	}

	/**
	 * Get Client Slug for Nation Builder.
	 *
	 * @return string
	 */
	public static function getClientSlugNationBuilder(): string
	{
		return \defined('ES_CLIENT_SLUG_NATIONBUILDER') ? \ES_CLIENT_SLUG_NATIONBUILDER : '';
	}

	/**
	 * Get Client ID for Nation Builder.
	 *
	 * @return string
	 */
	public static function getClientIdNationBuilder(): string
	{
		return \defined('ES_CLIENT_ID_NATIONBUILDER') ? \ES_CLIENT_ID_NATIONBUILDER : '';
	}

	/**
	 * Get Client Secret for Nation Builder.
	 *
	 * @return string
	 */
	public static function getClientSecretNationBuilder(): string
	{
		return \defined('ES_CLIENT_SECRET_NATIONBUILDER') ? \ES_CLIENT_SECRET_NATIONBUILDER : '';
	}
}
