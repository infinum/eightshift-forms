<?php
/**
 * Template for the Button Block view.
 *
 * @since 1.0.0
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;
$block_class = isset( $attributes['blockClass'] ) ? $attributes['blockClass'] : '';
$name        = isset( $attributes['name'] ) ? $attributes['name'] : '';
$value       = isset( $attributes['value'] ) ? $attributes['value'] : '';
$id          = isset( $attributes['id'] ) ? $attributes['id'] : '';
$classes     = isset( $attributes['classes'] )  ? $attributes['classes'] : '';
$type        = isset( $attributes['type'] )  ? $attributes['type'] : 'button';
$is_disabled = isset( $attributes['isDisabled'] ) && $attributes['isDisabled'] ? 'disabled' : '';

?>

<div class="<?php echo esc_attr( "{$block_class}" ); ?>">
  <?php if ( $type === 'button' ) { ?>
    <button
      name="<?php echo esc_attr( $name ); ?>"
      id="<?php echo esc_attr( $id ); ?>"
      class="<?php echo esc_attr( "{$classes} {$block_class}__button" ); ?>"
      <?php echo esc_attr( $is_disabled ); ?>
    >
      <?php echo esc_html( $value ); ?>
    </button>
  <?php } else { ?>
    <input
      name="<?php echo esc_attr( $name ); ?>"
      id="<?php echo esc_attr( $id ); ?>"
      class="<?php echo esc_attr( "{$classes} {$block_class}__button" ); ?>"
      value="<?php echo esc_attr( $value ); ?>"
      type="<?php echo esc_attr( $type ); ?>"
      <?php echo esc_attr( $is_disabled ); ?>
    />
  <?php } ?>
</div>
