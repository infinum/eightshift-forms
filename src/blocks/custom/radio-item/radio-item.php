<?php
/**
 * Template for the Radio Item Block view.
 *
 * @since 1.0.0
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

$block_class = isset( $attributes['blockClass'] ) ? $attributes['blockClass'] : '';
$label        = isset( $attributes['label'] ) ? $attributes['label'] : '';
$name        = isset( $attributes['name'] ) ? $attributes['name'] : '';
$value       = isset( $attributes['value'] ) ? $attributes['value'] : '';
$id          = isset( $attributes['id'] ) ? $attributes['id'] : '';
$classes     = isset( $attributes['classes'] )  ? $attributes['classes'] : '';
$is_checked  = isset( $attributes['isChecked'] ) && $attributes['isChecked'] ? 'checked' : '';
$is_disabled = isset( $attributes['isDisabled'] ) && $attributes['isDisabled'] ? 'disabled' : '';
$is_readOnly = isset( $attributes['isReadOnly'] ) && $attributes['isReadOnly'] ? 'readonly' : '';

?>

<div class="<?php echo esc_attr( "{$block_class}" ); ?>">
  <input
    name="<?php echo esc_attr( $name ); ?>"
    id="<?php echo esc_attr( $id ); ?>"
    class="<?php echo esc_attr( "{$classes} {$block_class}__radio" ); ?>"
    value="<?php echo esc_attr( $value ); ?>"
    type="radio"
    <?php echo esc_attr( $is_checked ); ?>
    <?php echo esc_attr( $is_disabled ); ?>
    <?php echo esc_attr( $is_readOnly ); ?>
  />
  <label
    for="<?php echo esc_attr( $id ); ?>"
    class="<?php echo esc_attr( "{$block_class}__label" ); ?>"
  >
    <?php echo esc_attr( $label ); ?>
  </label>
</div>
