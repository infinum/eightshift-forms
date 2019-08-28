<?php
/**
 * Template for the Wrapping Advance block.
 *
 * @since 1.0.0
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

// Used to add or remove wrapper.
$has_wrapper = $attributes['hasWrapper'] ?? true;

if ( $has_wrapper ) {

  $wrapper_main_class = 'field';

  $content_width = isset( $attributes['styleContentWidth'] ) ? "{$wrapper_main_class}__width--{$attributes['styleContentWidth']}" : '';

  $wrapper_class = "
    {$wrapper_main_class}
    {$content_width}
  ";

  ?>
  <div class="<?php echo esc_attr( $wrapper_class ); ?>">
    <?php
      $this->render_wrapper_view(
        $template_path,
        $attributes,
        $inner_block_content
      );
    ?>
  </div>
  <?php
} else {
  $this->render_wrapper_view(
    $template_path,
    $attributes,
    $inner_block_content
  );
}
