<?php
/**
 * Template for the Forms Block view.
 *
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

use Eightshift_Forms\View\Form_View;
use Eightshift_Forms\Helpers\Forms;

$block_class      = $attributes['blockClass'] ?? '';
$selected_form_id = $attributes['selectedFormId'] ?? 0;
$theme            = $attributes['theme'] ?? '';

// return;
error_log(print_r($theme, true));

$post_content = get_post_field( 'post_content', $selected_form_id );
$post_blocks  = Forms::add_theme_to_parsed_blocks( parse_blocks( $post_content ), $theme );

error_log(print_r($post_blocks, true));

foreach ( $post_blocks as $post_block ) {
  echo wp_kses( render_block( $post_block ), Form_View::allowed_tags() );
}


