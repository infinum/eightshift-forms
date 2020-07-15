<?php
/**
 * Template for the Forms Block view.
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

use Eightshift_Forms\View\Form_View;

$block_class      = $attributes['blockClass'] ?? '';
$selected_form_id = $attributes['selectedFormId'] ?? 0;

$post_content = get_post_field( 'post_content', $selected_form_id );
$post_blocks = parse_blocks( $post_content );

foreach( $post_blocks as $post_block ) {
  echo wp_kses( render_block( $post_block ), Form_View::allowed_tags() );
}

?>

