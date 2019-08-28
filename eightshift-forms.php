<?php
/**
 * Eightshift-forms main file starting point 
 *
 * @since             1.0.0
 * @package           Eightshift_Forms
 *
 * @wordpress-plugin
 * Plugin Name:       Eightshift Forms
 * Plugin URI:        https://github.com/infinum/eightshift-forms
 * Description:       This is Eightshift_Forms plugin used for contact forms.
 * Version:           1.0.0
 * Author URI:        https://eightshift.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       eightshift-forms
 * Requires PHP:      7.0
 */

namespace Eightshift_Forms;

use Eightshift_Forms\Core;

/*
 * Make sure this file is only run from within WordPress.
 */
defined( 'ABSPATH' ) || die();

/**
 * Include the autoloader so we can dynamically include the rest of the classes.
 *
 * @since 1.0.0
 */
$autoloader = __DIR__ . '/vendor/autoload.php';

if ( is_readable( $autoloader ) ) {
  require_once $autoloader;
}

/**
 * Plugin Dir const
 */
define( 'ES_FORMS_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Global variable defining plugin name generally used for naming assets handlers.
 *
 * @since 1.0.0
 */
define( 'ES_FORMS_PLUGIN_NAME', 'eightshift-forms' );

/**
 * Global variable defining plugin version generally used for versioning assets handlers.
 *
 * @since 1.0.0
 */
define( 'ES_FORMS_PLUGIN_VERSION', '1.0.0' );

// /**
//  * The code that runs during plugin activation.
//  *
//  * @since 1.0.0
//  */
// register_activation_hook(
//   __FILE__,
//   function() {
//     Plugin_Factory::create()->activate();
//   }
// );

// /**
//  * The code that runs during plugin deactivation.
//  *
//  * @since 1.0.0
//  */
// register_deactivation_hook(
//   __FILE__,
//   function() {
//     Plugin_Factory::create()->deactivate();
//   }
// );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the theme is registered via hooks,
 * then kicking off the theme from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
( new Core\Main() )->register();
