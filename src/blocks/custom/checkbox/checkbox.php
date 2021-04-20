<?php
/**
 * Template for the Checkbox Block view.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

use EightshiftLibs\Helpers\Components;

$block_class     = $attributes['blockClass'] ?? '';
$name            = $attributes['name'] ?? '';
$value           = $attributes['value'] ?? '';
$label           = $attributes['label'] ?? '';
$checkbox_id     = $attributes['id'] ?? '';
$classes         = $attributes['classes'] ?? '';
$theme           = $attributes['theme'] ?? '';
$is_checked      = isset( $attributes['isChecked'] ) && $attributes['isChecked'] ? 'checked' : '';
$is_disabled     = isset( $attributes['isDisabled'] ) && $attributes['isDisabled'] ? 'disabled' : '';
$is_read_only    = isset( $attributes['isReadOnly'] ) && $attributes['isReadOnly'] ? 'readonly' : '';
$is_required     = isset( $attributes['isRequired'] ) && $attributes['isRequired'] ? 'required' : '';
$prevent_sending = isset( $attributes['preventSending'] ) && $attributes['preventSending'] ? 'data-do-not-send' : '';

$block_classes = Components::classnames([
  $block_class,
  ! empty( $theme ) ? "{$block_class}__theme--{$theme}" : '',
])
?>

<div class="<?php echo esc_attr( $block_classes ); ?>">
  <label class="<?php echo esc_attr( "{$block_class}__label js-{$block_class}-label" ); ?>">
    <input
      name="<?php echo esc_attr( $name ); ?>"
      <?php ! empty( $checkbox_id ) ? printf( 'id="%s"', esc_attr( $checkbox_id ) ) : ''; ?>
      class="<?php echo esc_attr( "{$classes} {$block_class}__checkbox js-{$block_class}-checkbox" ); ?>"
      value="<?php echo esc_attr( $value ); ?>"
      type="checkbox"
      <?php echo esc_attr( $is_checked ); ?>
      <?php echo esc_attr( $is_disabled ); ?>
      <?php echo esc_attr( $is_read_only ); ?>
      <?php echo esc_attr( $is_required ); ?>
      <?php echo esc_attr( $prevent_sending ); ?>
    />
    <span class="<?php echo esc_attr( "{$block_class}__checkmark js-{$block_class}-checkmark" ); ?>"></span>
    <span class="<?php echo esc_attr( "{$block_class}__label-content" ); ?>">
      <?php echo wp_kses_post( $label ); ?>
    </span>
  </label>
</div>
