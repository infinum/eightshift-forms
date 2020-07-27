<?php
/**
 * Template for the Select Option Block view.
 *
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

$block_class = $attributes['blockClass'] ?? '';
$label       = $attributes['label'] ?? '';
$value       = $attributes['value'] ?? '';
$is_disabled = isset( $attributes['isDisabled'] ) && $attributes['isDisabled'] ? 'disabled' : '';
$is_selected = isset( $attributes['isSelected'] ) && $attributes['isSelected'] ? 'selected' : '';

?>

<option
  class="<?php echo esc_attr( "{$block_class}__option" ); ?>"
  value="<?php echo esc_attr( $value ); ?>"
  <?php echo esc_attr( $is_disabled ); ?>
  <?php echo esc_attr( $is_selected ); ?>
>
  <?php echo esc_html( $label ); ?>
</option>
