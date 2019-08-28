<?php
/**
 * Template for the Textarea Block view.
 *
 * @since 1.0.0
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

$block_class = isset( $attributes['blockClass'] ) ? $attributes['blockClass'] : '';
$name        = isset( $attributes['name'] ) ? $attributes['name'] : '';
$value       = isset( $attributes['value'] ) ? $attributes['value'] : '';
$id          = isset( $attributes['id'] ) ? $attributes['id'] : '';
$placeholder = isset( $attributes['placeholder'] ) ? $attributes['placeholder'] : '';
$classes     = isset( $attributes['classes'] )  ? $attributes['classes'] : '';
$rows        = isset( $attributes['rows'] ) ? $attributes['rows'] : '';
$cols        = isset( $attributes['cols'] ) ? $attributes['cols'] : '';
$is_disabled = isset( $attributes['isDisabled'] ) && $attributes['isDisabled'] ? 'disabled' : '';
$is_readOnly = isset( $attributes['isReadOnly'] ) && $attributes['isReadOnly'] ? 'readonly' : '';

?>

<div class="<?php echo esc_attr( "{$block_class}" ); ?>">
  <?php
    $this->render_block_view(
      '/components/label/label.php',
      [
        'blockClass' => $attributes['blockClass'] ?? '',
        'label'      => $attributes['label'] ?? '',
        'id'         => $attributes['id'] ?? '',
      ]
    );
  ?>
  <div class="<?php echo esc_attr( "{$block_class}__content-wrap" ); ?>">
    <textarea
      name="<?php echo esc_attr( $name ); ?>"
      placeholder="<?php echo esc_attr( $placeholder ); ?>"
      id="<?php echo esc_attr( $id ); ?>"
      class="<?php echo esc_attr( "{$classes} {$block_class}__textarea" ); ?>"
      value="<?php echo esc_attr( $value ); ?>"
      rows="<?php echo esc_attr( $rows ); ?>"
      cols="<?php echo esc_attr( $cols ); ?>"
      <?php echo esc_attr( $is_disabled ); ?>
      <?php echo esc_attr( $is_readOnly ); ?>
    ></textarea>
  </div>
</div>
