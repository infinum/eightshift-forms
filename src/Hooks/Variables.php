<?php

/**
 * The Variables class, used for defining available Variables
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
	 * Get API Key for Hubspot.
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
	 * Get API Key for Mailchimp.
	 *
	 * @return string
	 */
	public static function getApiKeyMailchimp(): string
	{
		return defined('ES_API_KEY_MAILCHIMP') ? ES_API_KEY_MAILCHIMP : '';
	}
}
