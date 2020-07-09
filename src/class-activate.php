<?php
/**
 * The file that defines actions on plugin activation.
 *
 * @package Eightshift_Forms\Core
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Core;

use Eightshift_Libs\Core\Has_Activation;

/**
 * The plugin activation class.
 */
class Activate implements Has_Activation {

  /**
   * Activate the plugin.
   */
  public function activate() : void {

    \flush_rewrite_rules();
  }
}
