<?php
/**
 * Template for the Radio Item Block view.
 *
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

$block_class   = $attributes['blockClass'] ?? '';
$label         = $attributes['label'] ?? '';
$name          = $attributes['name'] ?? '';
$value         = $attributes['value'] ?? '';
$radio_item_id = $attributes['id'] ?? '';
$classes       = $attributes['classes'] ?? '';
$is_checked    = isset( $attributes['isChecked'] ) && $attributes['isChecked'] ? 'checked' : '';
$is_disabled   = isset( $attributes['isDisabled'] ) && $attributes['isDisabled'] ? 'disabled' : '';
$is_read_only  = isset( $attributes['isReadOnly'] ) && $attributes['isReadOnly'] ? 'readonly' : '';

?>

<div class="<?php echo esc_attr( "{$block_class}" ); ?>">
  <input
    name="<?php echo esc_attr( $name ); ?>"
    <?php ! empty( $radio_item_id ) ? printf( 'id="%s"', esc_attr( $radio_item_id ) ) : ''; ?>
    class="<?php echo esc_attr( "{$classes} {$block_class}__radio" ); ?>"
    value="<?php echo esc_attr( $value ); ?>"
    type="radio"
    <?php echo esc_attr( $is_checked ); ?>
    <?php echo esc_attr( $is_disabled ); ?>
    <?php echo esc_attr( $is_read_only ); ?>
  />
  <label
    for="<?php echo esc_attr( $radio_item_id ); ?>"
    class="<?php echo esc_attr( "{$block_class}__label" ); ?>"
  >
    <?php echo esc_attr( $label ); ?>
  </label>
</div>
