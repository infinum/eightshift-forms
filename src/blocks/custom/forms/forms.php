<?php
/**
 * Template for the Forms Block view.
 *
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

use Eightshift_Forms\View\Form_View;
use Eightshift_Forms\Helpers\Forms;

function varexport($expression, $return=FALSE) {
  $export = var_export($expression, TRUE);
  $export = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $export);
  $array = preg_split("/\r\n|\n|\r/", $export);
  $array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
  $export = join(PHP_EOL, array_filter(["["] + $array));
  if ((bool)$return) return $export; else echo $export;
}

$block_class      = $attributes['blockClass'] ?? '';
$selected_form_id = $attributes['selectedFormId'] ?? 0;
$theme            = $attributes['theme'] ?? '';

$post_content = get_post_field( 'post_content', $selected_form_id );

if ( ! empty( $theme ) ) {
  $post_blocks = Forms::recursively_change_theme_for_all_blocks( parse_blocks( $post_content ), $theme );
} else {
  $post_blocks = parse_blocks( $post_content );
}

echo '<pre>';
echo varexport( $post_blocks );
echo '</pre>';

foreach ( $post_blocks as $post_block ) {
  echo wp_kses( render_block( $post_block ), Form_View::allowed_tags() );
}


