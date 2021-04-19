<?php
/**
 * Template for the Forms Block view.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

use EightshiftForms\View\Form_View;
use EightshiftForms\Helpers\Forms;

$block_class      = $attributes['blockClass'] ?? '';
$selected_form_id = $attributes['selectedFormId'] ?? 0;
$theme            = $attributes['theme'] ?? '';

$post_content = get_post_field( 'post_content', $selected_form_id );

if ( ! empty( $theme ) ) {
  $post_blocks = Forms::recursively_change_theme_for_all_blocks( parse_blocks( $post_content ), $theme );
} else {
  $post_blocks = parse_blocks( $post_content );
}

foreach ( $post_blocks as $post_block ) {
  echo wp_kses( render_block( $post_block ), Form_View::allowed_tags() );
}


