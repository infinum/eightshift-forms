<?php
/**
 * Plugin Name: Eightshift forms (new)
 * Plugin URI:
 * Description: Eightshift form builder plugin.
 * Author: Team Eightshift
 * Author URI: https://eightshift.com/
 * Version: 1.0.0
 * Text Domain: eightshift-forms
 *
 * @package Eightshift_Forms
 */

declare( strict_types=1 );

namespace Eightshift_Forms;

/**
 * If this file is called directly, abort.
 */
if ( ! \defined( 'WPINC' ) ) {
  die;
}

/**
 * Include the autoloader so we can dynamically include the rest of the classes.
 */
require __DIR__ . '/vendor/autoload.php';

/**
 * The code that runs during plugin activation.
 */
register_activation_hook(
  __FILE__,
  function() {
    ( new Core\Activate() )->activate();
  }
);

/**
 * The code that runs during plugin deactivation.
 */
register_deactivation_hook(
  __FILE__,
  function() {
    ( new Core\Deactivate() )->deactivate();
  }
);

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
( new Core\Main() )->register();
