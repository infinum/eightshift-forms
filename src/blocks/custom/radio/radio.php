<?php
/**
 * Template for the Radio Block view.
 *
 * @since 1.0.0
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

$block_class = isset( $attributes['blockClass'] ) ? $attributes['blockClass'] : '';

?>

<div class="<?php echo esc_attr( "{$block_class}" ); ?>">
  <?php
    $this->render_block_view(
      '/components/label/label.php',
      [
        'blockClass' => $attributes['blockClass'] ?? '',
        'label'      => $attributes['label'] ?? '',
      ]
    );
  ?>
  <div class="<?php echo esc_attr( "{$block_class}__content-wrap" ); ?>">
    <?php echo wp_kses_post( $inner_block_content ); ?>
  </div>
</div>
