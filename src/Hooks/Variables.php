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
	 * Set forms to develop mode.
	 *
	 * @return bool
	 */
	public static function isDevelopMode(): bool
	{
		return defined('ES_DEVELOP_MODE') ? true : false;
	}

	/**
	 * Set forms to skip validation used for development of integration.
	 *
	 * @return bool
	 */
	public static function skipFormValidation(): bool
	{
		return defined('ES_DEVELOP_MODE_SKIP_VALIDATION') ? true : false;
	}

	/**
	 * Set forms to log out requests/responses.
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
}
