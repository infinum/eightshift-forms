<?php
/**
 * Template for the Label Component.
 *
 * @since 1.0.0
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

$block_class = $attributes['blockClass'] ?? '';
$label       = $attributes['label'] ?? '';
$id          = $attributes['id'] ?? '';

$component_class = 'label';

?>

<div class="<?php echo esc_attr( "{$component_class}__label-wrap {$block_class}__label-wrap" ); ?>">
  <label for="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( "{$component_class} {$block_class}__label" ); ?>">
    <?php echo esc_html( $label ); ?>
  </label>
</div>
