<?php
/**
 * Helpers for components
 *
 * @package Eightshift_Forms\Helpers
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Helpers;

use Eightshift_Forms\Core\Config;
use Eightshift_Libs\Helpers\Components as Libs_Components;

/**
 * Helpers for components
 */
class Components extends Libs_Components {

  /**
   * Wrapper for libs components so we don't have to pass the path each time.
   *
   * @param  string $component   Component's name or full path (ending with .php).
   * @param  array  $attributes  Array of attributes that's implicitly passed to component.
   * @param  string $parent_path If parent path is provides it will be appended to the file location, if not get_template_directory_uri() will be used as a default parent path.
   * @return string
   *
   * @throws \Exception When we're unable to find the component by $component.
   */
  public static function render( string $component, array $attributes = [], string $parent_path = '' ) {
    $parent_path = Config::get_project_path();
    return Libs_Components::render( $component, $attributes, $parent_path );
  }
}
