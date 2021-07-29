<?php

/**
 * Plugin Name: Eightshift forms
 * Plugin URI:
 * Description: Eightshift form builder plugin.
 * Author: Team Eightshift
 * Author URI: https://eightshift.com/
 * Version: 0.3.1
 * Text Domain: eightshift-forms
 *
 * @package EightshiftForms
 */

declare(strict_types=1);

namespace EightshiftForms;

use EightshiftLibs\Cli\Cli;
use EightshiftForms\Main\Main;

/**
 * If this file is called directly, abort.
 */
if (! \defined('WPINC')) {
	die;
}

/**
 * Include the autoloader so we can dynamically include the rest of the classes.
 */
$loader = require __DIR__ . '/vendor/autoload.php';

/**
 * The code that runs during plugin activation.
 */
register_activation_hook(
	__FILE__,
	function () {
		PluginFactory::activate();
	}
);

/**
 * The code that runs during plugin deactivation.
 */
register_deactivation_hook(
	__FILE__,
	function () {
		PluginFactory::deactivate();
	}
);

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
if (class_exists(Main::class)) {
	(new Main($loader->getPrefixesPsr4(), __NAMESPACE__))->register();
}

/**
 * Run all WPCLI commands.
 */
if (class_exists(Cli::class)) {
	(new Cli())->load('eightshift-forms');
}
