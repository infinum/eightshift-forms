<?php
/**
 * The file that defines the project entry point class.
 *
 * A class definition that includes attributes and functions used across both the
 * public side of the site and the admin area.
 *
 * @package Eightshift_Forms\Core
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Core;

use Eightshift_Libs\Core\Config as Lib_Config;

/**
 * The project config class.
 */
class Config extends Lib_Config {

  /**
   * Value for the form type when it's submitting to Dynamics CRM.
   * 
   * @var string
   */
  const DYNAMICS_CRM_METHOD = 'dynamics-crm';

  /**
   * Method that returns project name.
   *
   * Generally used for naming assets handlers, languages, etc.
   */
  public static function get_project_name() : string {
    return 'eightshift-forms';
  }

  /**
   * Method that returns project version.
   *
   * Generally used for versioning asset handlers while enqueueing them.
   */
  public static function get_project_version() : string {
    return '1.0.0';
  }

  /**
   * Method that returns project routes version.
   */
  public static function get_project_routes_version() : string {
    return 'v1';
  }

  /**
   * Method that returns project prefix.
   *
   * The WordPress filters live in a global namespace, so we need to prefix them to avoid naming collisions.
   *
   * @return string Full path to asset.
   */
  public static function get_project_prefix() : string {
    return 'ef';
  }

  /**
   * Return project absolute path.
   *
   * If used in a theme use get_template_directory() and in case it's used in a plugin use __DIR__.
   *
   * @param string $path Additional path to add to project path.
   *
   * @return string
   */
  public static function get_project_path( string $path = '' ) : string {
    return rtrim( plugin_dir_path( __DIR__ ), '/' ) . $path;
  }
}
