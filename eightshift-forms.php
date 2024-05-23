<?php

/**
 * Plugin Name: Eightshift Forms
 * Plugin URI: https://github.com/infinum/eightshift-forms
 * Description: Eightshift Forms is a complete form builder plugin that utilizes modern Block editor features with multiple third-party integrations, bringing your project to a new level.
 * Author: WordPress team @Infinum
 * Author URI: https://eightshift.com/
 * Version: 4.0.1-alpha
 * Text Domain: eightshift-forms
 *
 * @package EightshiftForms
 */

declare(strict_types=1);

namespace EightshiftForms;

use EightshiftForms\Main\Main;
use EightshiftForms\Testfilters\Testfilters;
use EightshiftForms\Cache\ManifestCache;

/**
 * If this file is called directly, abort.
 */
if (! \defined('WPINC')) {
	die;
}

/**
 * Bailout, if the plugin is not loaded via Composer.
 */
$autoloadPath = __DIR__ . '/vendor/autoload.php';
$autoloadVendorPath = __DIR__ . '/vendor-prefixed/autoload.php';

if (!\file_exists($autoloadPath) || !\file_exists($autoloadVendorPath)) {
	return;
}

/**
 * Require the Composer autoloader.
 */
$loader = require $autoloadPath;
require __DIR__ . '/vendor-prefixed/autoload.php';

if (\class_exists(PluginFactory::class)) {
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
}

/**
 * Set all the cache for the plugin.
 */
if (\class_exists(ManifestCache::class)) {
	(new ManifestCache())->setAllCache();
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
if (\class_exists(Main::class)) {
	$sep = \DIRECTORY_SEPARATOR;

	(new Main($loader->getPrefixesPsr4(), __NAMESPACE__))->register();

	// Require public helper class.
	require __DIR__ . "{$sep}src{$sep}Helpers{$sep}publicHelper.php";

	// Require test filters.
	require __DIR__ . "{$sep}testFilters{$sep}testFilters.php";

	(new Testfilters())->register();
}
