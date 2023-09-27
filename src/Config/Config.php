<?php

/**
 * The file that defines the project entry point class.
 *
 * A class definition that includes attributes and functions used across both the
 * public side of the site and the admin area.
 *
 * @package EightshiftForms\Config
 */

declare(strict_types=1);

namespace EightshiftForms\Config;

use EightshiftFormsVendor\EightshiftLibs\Config\AbstractConfigData;

/**
 * The project config class.
 */
class Config extends AbstractConfigData
{
	/**
	 * Method that returns project name.
	 *
	 * Generally used for naming assets handlers, languages, etc.
	 */
	public static function getProjectName(): string
	{
		return "eightshift-forms";
	}

	/**
	 * Method that returns projects setting name prefix.
	 *
	 * @return string
	 */
	public static function getSettingNamePrefix(): string
	{
		return "es-forms";
	}

	/**
	 * Method that returns projects temp upload dir name.
	 *
	 * @return string
	 */
	public static function getTempUploadDir(): string
	{
		return "esforms-tmp";
	}

	/**
	 * Method that returns project version.
	 *
	 * Generally used for versioning asset handlers while enqueueing them.
	 */
	public static function getProjectVersion(): string
	{
		if (!\function_exists('get_plugin_data')) {
			require_once(\ABSPATH . 'wp-admin/includes/plugin.php');
		}

		$details = \get_plugin_data(\dirname(__FILE__, 3) . '/eightshift-forms.php');

		return isset($details['Version']) ? (string) $details['Version'] : '1.0.0';
	}

	/**
	 * Method that returns project REST-API namespace.
	 *
	 * Used for namespacing projects REST-API routes and fields.
	 *
	 * @return string Project name.
	 */
	public static function getProjectRoutesNamespace(): string
	{
		return static::getProjectName();
	}

	/**
	 * Method that returns project REST-API version.
	 *
	 * Used for versioning projects REST-API routes and fields.
	 *
	 * @return string Project route version.
	 */
	public static function getProjectRoutesVersion(): string
	{
		return 'v1';
	}
}
