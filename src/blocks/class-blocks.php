<?php
/**
 * Blocks class used to define configurations for blocks.
 *
 * @since   1.0.0
 * @package Eightshift_Forms\Blocks
 */

namespace Eightshift_Forms\Blocks;

use Eightshift_Blocks\Blocks as Lib_Blocks;
use Eightshift_Forms\Admin\Forms;

/**
 * Blocks class.
 */
class Blocks extends Lib_Blocks {

  /**
   * Register all the hooks
   *
   * @since 1.0.0
   *
   * @return void
   */
  public function register() {
    parent::register();

    add_filter( 'allowed_block_types', [ $this, 'get_all_allowed_forms_blocks' ], 12, 2 );
  }

  /**
   * Limit block on forms post type to internal plugin blocks
   *
   * @param bool|array $allowed_block_types Array of block type slugs, or boolean to enable/disable all.
   * @param object $post The post resource data.
   *
   * @return array
   *
   * @since 1.0.0
   */
  public function get_all_allowed_forms_blocks( $allowed_block_types, object $post ) {
    if ( $post->post_type === Forms::POST_TYPE_SLUG ) {
      return $this->get_all_blocks_list();
    }
  }

  /**
   * Get global blocks settings variable name to store the cached data into.
   *
   * @return string
   *
   * @since 1.0.0
   */
  protected function get_blocks_settings_variable_name() : string {
    return 'ES_FORMS_BLOCKS_SETTINGS';
  }


  /**
   * Get wrapper settings variable name to store the cached data into.
   *
   * @return string
   *
   * @since 1.0.0
   */
  protected function get_wrapper_variable_name() : string {
    return 'ES_FORMS_WRAPPER';
  }


  /**
   * Get global blocks variable name to store the cached data into.
   *
   * @return string
   *
   * @since 1.0.0
   */
  protected function get_blocks_variable_name() : string {
    return 'ES_FORMS_BLOCKS';
  }


  /**
   * Get Projects Theme path.
   * If you are using a plugin, override method must be provided with correct blocks folder.
   *
   * @return string
   *
   * @since 1.0.7 Removing static method.
   * @since 1.0.0
   */
  public function get_blocks_path() : string {
    return ES_FORMS_PLUGIN_DIR_PATH . 'src/blocks';
  }
}
