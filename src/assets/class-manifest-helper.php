<?php
/**
 * The Manifest Helper specific functionality.
 * Important note: Use only in views where you cant control rendering methods.
 *
 * @since   1.0.0 Init class.
 * @package Eightshift_Forms\Assets
 */

namespace Eightshift_Forms\Assets;

use Eightshift_Forms\Assets\Manifest;

/**
 * Class Manifest_Helper
 */
class Manifest_Helper {

  /**
   * Get assets manifest value by providing a object key.
   *
   * @param string $key File name key you want to get from manifest.
   *
   * @return string
   *
   * @since 4.0.0 Init.
   */
  public static function get_assets_manifest_item( string $key ) : string {
    $manifest = new Manifest();
    return $manifest->get_assets_manifest_item( $key );
  }
}
