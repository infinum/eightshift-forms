<?php

/**
 * The file that defines the main start class.
 *
 * A class definition that includes attributes and functions used across both the
 * theme-facing side of the site and the admin area.
 *
 * @package EightshiftForms\Main
 */

declare(strict_types=1);

namespace EightshiftForms\Main;

use EightshiftForms\Hooks\Filters;
use EightshiftFormsVendor\EightshiftFormsUtils\Config\UtilsConfig;
use EightshiftFormsVendor\EightshiftLibs\Main\AbstractMain;

/**
 * The main start class.
 *
 * This is used to define admin-specific hooks, and
 * theme-facing site hooks.
 *
 * Also maintains the unique identifier of this theme as well as the current
 * version of the theme.
 */
class Main extends AbstractMain
{
	/**
	 * Register the project with the WordPress system.
	 *
	 * The register_service method will call the register() method in every service class,
	 * which holds the actions and filters - effectively replacing the need to manually add
	 * them in one place.
	 *
	 * @return void
	 */
	public function register(): void
	{
		\add_action('plugins_loaded', [$this, 'registerServices']);
	}

	/**
	 * Reguster all the services and trigger custom action hook used for addons.
	 *
	 * @return void
	 */
	public function registerServices(): void
	{
		// Define global variable for all public filters/actions.
		if (!\defined('EIGHTSHIFT_FORMS')) {
			\define('EIGHTSHIFT_FORMS', Filters::getHooksData());
		}

		// Normal service registration.
		parent::registerServices();

		// Filter triggered when main form's plugins is loaded to hook add-ons.
		\do_action(UtilsConfig::FILTER_LOADED_NAME);
	}
}
