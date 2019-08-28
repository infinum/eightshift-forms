<?php
/**
 * Template for the Form Block view.
 *
 * @since 1.0.0
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

$block_class = isset( $attributes['blockClass'] ) ? $attributes['blockClass'] : '';
$action      = isset( $attributes['action'] ) ? $attributes['action'] : '';
$method      = isset( $attributes['method'] ) ? $attributes['method'] : '';
$target      = isset( $attributes['target'] ) ? $attributes['target'] : '';
$classes     = isset( $attributes['classes'] ) ? $attributes['classes'] : '';
$id          = isset( $attributes['id'] ) ? $attributes['id'] : '';
$action      = isset( $attributes['action'] ) ? $attributes['action'] : '';
$action      = isset( $attributes['action'] ) ? $attributes['action'] : '';

?>

<form
  class="<?php echo esc_attr( "{$block_class} {$classes}" ); ?>"
  action="<?php echo esc_attr( "{$action}" ); ?>"
  method="<?php echo esc_attr( "{$method}" ); ?>"
  target="<?php echo esc_attr( "{$target}" ); ?>"
  id="<?php echo esc_attr( "{$id}" ); ?>"
>
  <?php echo wp_kses_post( $inner_block_content ); ?>
</form>
