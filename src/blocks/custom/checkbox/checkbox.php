<?php
/**
 * Template for the Checkbox Block view.
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

$block_class = $attributes['blockClass'] ?? '';
$name        = $attributes['name'] ?? '';
$value       = $attributes['value'] ?? '';
$description = $attributes['description'] ?? '';
$id          = $attributes['id'] ?? '';
$classes     = $attributes['classes'] ?? '';
$is_checked  = $attributes['isChecked'] ?? 'checked';
$is_disabled = $attributes['isDisabled'] ?? 'disabled';
$is_readOnly = $attributes['isReadOnly'] ?? 'readonly';

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
    <input
      name="<?php echo esc_attr( $name ); ?>"
      <?php ! empty( $id ) ? sprintf('id="%s"', esc_attr( $id ) ): '' ?>
      class="<?php echo esc_attr( "{$classes} {$block_class}__checkbox" ); ?>"
      value="<?php echo esc_attr( $value ); ?>"
      type="checkbox"
      <?php echo esc_attr( $is_checked ); ?>
      <?php echo esc_attr( $is_disabled ); ?>
      <?php echo esc_attr( $is_readOnly ); ?>
    />
    <p class="<?php echo esc_attr( "{$block_class}__description" ); ?>">
      <?php echo esc_html( $description ); ?>
    </p>
  </div>
</div>
