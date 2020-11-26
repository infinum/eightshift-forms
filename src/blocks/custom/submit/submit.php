<?php
/**
 * Template for the Submit Block view.
 *
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

use Eightshift_Libs\Helpers\Components;

$block_class = $attributes['blockClass'] ?? '';
$name        = $attributes['name'] ?? '';
$value       = $attributes['value'] ?? '';
$submit_id   = $attributes['id'] ?? '';
$classes     = $attributes['classes'] ?? '';
$theme       = $attributes['theme'] ?? '';
$submit_type = isset( $attributes['type'] ) ? $attributes['type'] : 'submit';
$is_disabled = isset( $attributes['isDisabled'] ) && $attributes['isDisabled'] ? 'disabled' : '';

$block_classes = Components::classnames([
  $block_class,
  ! empty( $theme ) ? "{$block_class}__theme--{$theme}" : '',
  ! empty( $is_disabled ) ? "{$block_class}--is-disabled" : '',
]);
?>

<div class="<?php echo esc_attr( $block_classes ); ?>">
  <?php if ( $submit_type === 'button' ) { ?>
    <button
      name="<?php echo esc_attr( $name ); ?>"
      <?php ! empty( $submit_id ) ? printf( 'id="%s"', esc_attr( $submit_id ) ) : ''; ?>
      class="<?php echo esc_attr( "{$classes} {$block_class}__button" ); ?>"
      <?php echo esc_attr( $is_disabled ); ?>
    >
      <?php echo esc_html( $value ); ?>
    </button>
  <?php } else { ?>
    <input
      name="<?php echo esc_attr( $name ); ?>"
      <?php ! empty( $submit_id ) ? printf( 'id="%s"', esc_attr( $submit_id ) ) : ''; ?>
      class="<?php echo esc_attr( "{$classes} {$block_class}__input" ); ?>"
      value="<?php echo esc_attr( $value ); ?>"
      type="<?php echo esc_attr( $submit_type ); ?>"
      <?php echo esc_attr( $is_disabled ); ?>
    />
  <?php } ?>
</div>
