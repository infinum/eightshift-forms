<?php

/**
 * Class that holds all developer helpers.
 *
 * @package EightshiftForms\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

use EightshiftForms\Config\Config;

/**
 * Class DeveloperHelpers
 */
final class DeveloperHelpers
{
	/**
	 * Check if developer debugging is active.
	 *
	 * @return boolean
	 */
	public static function isDeveloperDebuggingActive(): bool
	{
		return \apply_filters(Config::FILTER_SETTINGS_IS_DEBUG_ACTIVE, false, Config::SETTINGS_DEBUG_DEBUGGING_KEY);
	}

	/**
	 * Check if developer skip form validation is active.
	 *
	 * @return boolean
	 */
	public static function isDeveloperSkipFormValidationActive(): bool
	{
		return \apply_filters(Config::FILTER_SETTINGS_IS_DEBUG_ACTIVE, false, Config::SETTINGS_DEBUG_SKIP_VALIDATION_KEY);
	}

	/**
	 * Check if developer skip form reset is active.
	 *
	 * @return boolean
	 */
	public static function isDeveloperSkipFormResetActive(): bool
	{
		return \apply_filters(Config::FILTER_SETTINGS_IS_DEBUG_ACTIVE, false, Config::SETTINGS_DEBUG_SKIP_RESET_KEY);
	}

	/**
	 * Check if developer skip captcha is active.
	 *
	 * @return boolean
	 */
	public static function isDeveloperSkipCaptchaActive(): bool
	{
		return \apply_filters(Config::FILTER_SETTINGS_IS_DEBUG_ACTIVE, false, Config::SETTINGS_DEBUG_SKIP_CAPTCHA_KEY);
	}

	/**
	 * Check if developer skip forms sync is active.
	 *
	 * @return boolean
	 */
	public static function isDeveloperSkipFormsSyncActive(): bool
	{
		return \apply_filters(Config::FILTER_SETTINGS_IS_DEBUG_ACTIVE, false, Config::SETTINGS_DEBUG_SKIP_FORMS_SYNC_KEY);
	}

	/**
	 * Check if developer skip cache is active.
	 *
	 * @return boolean
	 */
	public static function isDeveloperSkipCacheActive(): bool
	{
		return \apply_filters(Config::FILTER_SETTINGS_IS_DEBUG_ACTIVE, false, Config::SETTINGS_DEBUG_SKIP_CACHE_KEY);
	}

	/**
	 * Check if developer mode is active.
	 *
	 * @return boolean
	 */
	public static function isDeveloperModeActive(): bool
	{
		return \apply_filters(Config::FILTER_SETTINGS_IS_DEBUG_ACTIVE, false, Config::SETTINGS_DEBUG_DEVELOPER_MODE_KEY);
	}
}
