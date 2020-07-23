<?php
/**
 * The file that defines the main start class.
 *
 * A class definition that includes attributes and functions used across both the
 * theme-facing side of the site and the admin area.
 *
 * @package Eightshift_Forms\Core
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Core;

use Eightshift_Libs\Core\Main as Lib_Core;
use Eightshift_Libs\Manifest as Lib_Manifest;
use Eightshift_Libs\Enqueue as Lib_Enqueue;
use Eightshift_Libs\I18n as Lib_I18n;
use Eightshift_Forms\Admin;
use Eightshift_Forms\Blocks;
use Eightshift_Forms\Blocks\Enqueue as BlocksEnqueue;
use Eightshift_Forms\Rest;
use Eightshift_Forms\Enqueue;
use Eightshift_Forms\Enqueue\Localization_Constants;
use Eightshift_Forms\View;
use Eightshift_Forms\Integrations;
/**
 * The main start class.
 *
 * This is used to define admin-specific hooks, and
 * theme-facing site hooks.
 *
 * Also maintains the unique identifier of this theme as well as the current
 * version of the theme.
 */
class Main extends Lib_Core {

  /**
   * Default main action hook that start the whole lib. If you are using this lib in a plugin please change it to plugins_loaded.
   */
  public function get_default_register_action_hook() : string {
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

      // Config.
      Config::class,

      // Manifest.
      Lib_Manifest\Manifest::class => [ Config::class ],

      // I18n.
      Lib_I18n\I18n::class => [ Config::class ],

      // Dynamics CRM
      Integrations\OAuth2_Client::class,
      Integrations\Dynamics_CRM::class => [
        Integrations\OAuth2_Client::class
      ],
      Rest\Dynamics_Crm_Route::class => [
        Config::class,
        Integrations\Dynamics_CRM::class,
      ],

      // Enqueue.
      Localization_Constants::class => [
        Lib_Manifest\Manifest::class,
        Rest\Dynamics_Crm_Route::class
      ],
      Enqueue\Enqueue_Theme::class => [
        Lib_Manifest\Manifest::class,
        Enqueue\Localization_Constants::class,
      ],
      Enqueue\Enqueue_Blocks::class => [
        Lib_Manifest\Manifest::class,
        Enqueue\Localization_Constants::class,
      ],

      // Admin
      Admin\Forms::class,
      Admin\Content::class,

      // Blocks.
      Blocks\Blocks::class => [ Config::class ],

      // View
      View\Post_View_Filter::class,
    ];
  }
}
