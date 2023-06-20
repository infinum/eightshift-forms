<?php

/**
 * Plugin Name: Eightshift Forms
 * Plugin URI: https://github.com/infinum/eightshift-forms
 * Description: Eightshift Forms is a complete form builder plugin that utilizes modern Block editor features with multiple third-party integrations, bringing your project to a new level.
 * Author: WordPress team @Infinum
 * Author URI: https://eightshift.com/
 * Version: 4.0.42
 * Text Domain: eightshift-forms
 *
 * @package EightshiftForms
 */

declare(strict_types=1);

namespace EightshiftForms;

use EightshiftForms\Main\Main;
use EightshiftForms\Testfilters\Testfilters;

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
\register_activation_hook(
	__FILE__,
	function () {
		PluginFactory::activate();
	}
);

/**
 * The code that runs during plugin deactivation.
 */
\register_deactivation_hook(
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
if (\class_exists(Main::class)) {
	(new Main($loader->getPrefixesPsr4(), __NAMESPACE__))->register();

	// Require public helper class.
	require __DIR__ . '/src/Helpers/esForms.php';

	// Require public helper class.
	require __DIR__ . '/testFilters/testFilters.php';

	(new Testfilters())->register();
}
