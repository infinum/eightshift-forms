<?php
/**
 * The file that defines actions on plugin deactivation.
 *
 * @package Eightshift_Forms\Core
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Core;

use Eightshift_Libs\Core\Has_Deactivation;

/**
 * The plugin deactivation class.
 */
class Deactivate implements Has_Deactivation {

  /**
   * Deactivate the plugin.
   */
  public function deactivate() : void {

    \flush_rewrite_rules();
  }
}
