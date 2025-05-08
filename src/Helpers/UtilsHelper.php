<?php

/**
 * Class that holds all utils helpers.
 *
 * @package EightshiftForms\Helpers
 */

declare(strict_types=1);

namespace EightshiftForms\Helpers;

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

/**
 * UtilsHelper class.
 */
class UtilsHelper
{
	/**
	 * Return utils icons from manifest.json.
	 *
	 * @param string $type Type to return.
	 *
	 * @return string
	 */
	public static function getUtilsIcons(string $type): string
	{
		return Helpers::getSettings()['icons'][Helpers::kebabToCamelCase($type)] ?? '';
	}

	/**
	 * Return selector admin enum value by name.
	 *
	 * @param string $name Name of the enum.
	 *
	 * @return string
	 */
	public static function getStateSelectorAdmin(string $name): string
	{
		return Helpers::getSettings()['enums']['selectorsAdmin'][$name] ?? '';
	}

	/**
	 * Return selector enum value by name.
	 *
	 * @param string $name Name of the enum.
	 *
	 * @return string
	 */
	public static function getStateSelector(string $name): string
	{
		return Helpers::getSettings()['enums']['selectors'][$name] ?? '';
	}

	/**
	 * Return attribute enum value by name.
	 *
	 * @param string $name Name of the enum.
	 *
	 * @return string
	 */
	public static function getStateAttribute(string $name): string
	{
		return Helpers::getSettings()['enums']['attrs'][$name] ?? '';
	}

	/**
	 * Return all params enum values.
	 *
	 * @return array<string>
	 */
	public static function getStateParams(): array
	{
		return Helpers::getSettings()['enums']['params'] ?? [];
	}

	/**
	 * Return param enum value by name.
	 *
	 * @param string $name Name of the enum.
	 *
	 * @return string
	 */
	public static function getStateParam(string $name): string
	{
		return self::getStateParams()[$name] ?? '';
	}

	/**
	 * Return all responseOutputKeys enum values.
	 *
	 * @return array<string>
	 */
	public static function getStateResponseOutputKeys(): array
	{
		return Helpers::getSettings()['enums']['responseOutputKeys'] ?? [];
	}

	/**
	 * Return responseOutputKeys enum value by name.
	 *
	 * @param string $name Name of the enum.
	 *
	 * @return string
	 */
	public static function getStateResponseOutputKey(string $name): string
	{
		return self::getStateResponseOutputKeys()[$name] ?? '';
	}

	/**
	 * Return successRedirectUrlKeys enum values.
	 *
	 * @return array<string>
	 */
	public static function getStateSuccessRedirectUrlKeys(): array
	{
		return Helpers::getSettings()['enums']['successRedirectUrlKeys'] ?? [];
	}

	/**
	 * Return successRedirectUrlKeys enum value by name.
	 *
	 * @param string $name Name of the enum.
	 *
	 * @return string
	 */
	public static function getStateSuccessRedirectUrlKey(string $name): string
	{
		return self::getStateSuccessRedirectUrlKeys()[$name] ?? '';
	}
}
