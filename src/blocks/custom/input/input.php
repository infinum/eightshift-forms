<?php
/**
 * Template for the Input Block view.
 *
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

$block_class  = $attributes['blockClass'] ?? '';
$name         = $attributes['name'] ?? '';
$value        = $attributes['value'] ?? '';
$input_id     = $attributes['id'] ?? '';
$placeholder  = $attributes['placeholder'] ?? '';
$classes      = $attributes['classes'] ?? '';
$theme        = $attributes['theme'] ?? '';
$input_type   = $attributes['type'] ?? '';
$is_disabled  = isset( $attributes['isDisabled'] ) && $attributes['isDisabled'] ? 'disabled' : '';
$is_read_only = isset( $attributes['isReadOnly'] ) && $attributes['isReadOnly'] ? 'readonly' : '';
$is_required  = isset( $attributes['isRequired'] ) && $attributes['isRequired'] ? 'required' : '';

?>

<div class="<?php echo esc_attr( "{$block_class}" ); ?>">
  <?php
    $this->render_block_view(
      '/components/label/label.php',
      array(
        'blockClass' => $attributes['blockClass'] ?? '',
        'label'      => $attributes['label'] ?? '',
        'id'         => $attributes['id'] ?? '',
      )
    );
    ?>
  <div class="<?php echo esc_attr( "{$block_class}__content-wrap {$block_class}__theme--{$theme}" ); ?>">
    <input
      name="<?php echo esc_attr( $name ); ?>"
      placeholder="<?php echo esc_attr( $placeholder ); ?>"
      <?php ! empty( $input_id ) ? printf( 'id="%s"', esc_attr( $input_id ) ) : ''; ?>
      class="<?php echo esc_attr( "{$classes} {$block_class}__input" ); ?>"
      value="<?php echo esc_attr( $value ); ?>"
      type="<?php echo esc_attr( $input_type ); ?>"
      <?php echo esc_attr( $is_disabled ); ?>
      <?php echo esc_attr( $is_read_only ); ?>
      <?php echo esc_attr( $is_required ); ?>
    />
  </div>
</div>
