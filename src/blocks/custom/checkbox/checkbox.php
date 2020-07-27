<?php
/**
 * Template for the Checkbox Block view.
 *
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

use Eightshift_Libs\Helpers\Components;

$block_class = $attributes['blockClass'] ?? '';
$name        = $attributes['name'] ?? '';
$value       = $attributes['value'] ?? '';
$description = $attributes['description'] ?? '';
$checkbox_id = $attributes['id'] ?? '';
$classes     = $attributes['classes'] ?? '';
$theme       = $attributes['theme'] ?? '';
$is_checked  = isset( $attributes['isChecked'] ) && $attributes['isChecked'] ? 'checked' : '';
$is_disabled = isset( $attributes['isDisabled'] ) && $attributes['isDisabled'] ? 'disabled' : '';
$is_read_only = isset( $attributes['isReadOnly'] ) && $attributes['isReadOnly'] ? 'readonly' : '';
$is_required = isset( $attributes['isRequired'] ) && $attributes['isRequired'] ? 'required' : '';

$block_classes = Components::classnames([
  $block_class,
  ! empty( $theme ) ? "{$block_class}__theme--{$theme}" : '',
])
?>

<div class="<?php echo esc_attr( $block_classes ); ?>">
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
  <div class="<?php echo esc_attr( "{$block_class}__content-wrap" ); ?>">
    <input
      name="<?php echo esc_attr( $name ); ?>"
      <?php ! empty( $checkbox_id ) ? printf( 'id="%s"', esc_attr( $checkbox_id ) ) : ''; ?>
      class="<?php echo esc_attr( "{$classes} {$block_class}__checkbox" ); ?>"
      value="<?php echo esc_attr( $value ); ?>"
      type="checkbox"
      <?php echo esc_attr( $is_checked ); ?>
      <?php echo esc_attr( $is_disabled ); ?>
      <?php echo esc_attr( $is_read_only ); ?>
      <?php echo esc_attr( $is_required ); ?>
    />
    <p class="<?php echo esc_attr( "{$block_class}__description" ); ?>">
      <?php echo wp_kses_post( $description ); ?>
    </p>
  </div>
</div>
