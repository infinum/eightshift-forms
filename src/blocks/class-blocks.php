<?php
/**
 * Blocks class used to define configurations for blocks.
 *
 * @package Eightshift_Forms\Blocks
 */

namespace Eightshift_Forms\Blocks;

use Eightshift_Libs\Blocks\Blocks as Lib_Blocks;
use Eightshift_Forms\Admin\Forms;
use Eightshift_Forms\Hooks\Filters;

/**
 * Blocks class.
 */
class Blocks extends Lib_Blocks implements Filters {

  /**
   * Register all the hooks
   *
   * @return void
   */
  public function register() {
    parent::register();

    add_filter( 'allowed_block_types', array( $this, 'get_all_allowed_forms_blocks' ), 20, 2 );
  }

  /**
   * Limit block on forms post type to internal plugin blocks
   *
   * @param bool|array $allowed_block_types Array of block type slugs, or boolean to enable/disable all.
   * @param object     $post The post resource data.
   *
   * @return array
   */
  public function get_all_allowed_forms_blocks( $allowed_block_types, $post ) {
    if ( $post->post_type === Forms::POST_TYPE_SLUG ) {
      if ( has_filter( self::ALLOWED_BLOCKS ) ) {
        return apply_filters( self::ALLOWED_BLOCKS, $this->get_all_blocks_list() );
      } else {
        return $this->get_all_blocks_list();
      }
    }

    $allowed_block_types[] = "{$this->config->get_project_name()}/forms";

    return $allowed_block_types;
  }

  /**
   * Create custom category to assign all custom blocks.
   * This category will show on all blocks list in "Add Block" button.
   *
   * @param array $categories Array of all blocks categories.
   * @return array
   */
  public function get_custom_category( $categories ) {
    return $categories;
  }
}
