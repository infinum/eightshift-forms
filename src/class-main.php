<?php
/**
 * The file that defines the main start class.
 *
 * A class definition that includes attributes and functions used across both the
 * theme-facing side of the site and the admin area.
 *
 * @since 1.0.0
 * @package Eightshift_Forms\Core
 */

namespace Eightshift_Forms\Core;

use Eightshift_Libs\Core\Main as LibMain;

use Eightshift_Forms\Admin;
use Eightshift_Forms\Assets;
use Eightshift_Forms\Blocks;

/**
 * The main start class.
 *
 * This is used to define admin-specific hooks, and
 * theme-facing site hooks.
 *
 * Also maintains the unique identifier of this theme as well as the current
 * version of the theme.
 */
class Main extends LibMain {

    /**
   * Returns Theme/Plugin main action hook that start the whole lib.
   *
   * @return string
   *
   * @since 1.0.0
   */
  public function get_register_action_hook() : string {
    return 'plugins_loaded';
  }

  /**
   * Get the list of services to register.
   *
   * A list of classes which contain hooks.
   *
   * @return array<string> Array of fully qualified class names.
   */
  protected function get_service_classes() : array {
    return [

      // Admin.
      Admin\Forms::class,
      Admin\Content::class,

      // Assets.
      Assets\Manifest::class,

      // Blocks.
      Blocks\Enqueue::class => [ Assets\Manifest::class ],
      Blocks\Blocks::class,
    ];
  }
}
