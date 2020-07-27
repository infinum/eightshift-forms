<?php
/**
 * Template for the Select Block view.
 *
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

$block_class = $attributes['blockClass'] ?? '';
$name        = $attributes['name'] ?? '';
$select_id   = $attributes['id'] ?? '';
$classes     = $attributes['classes'] ?? '';
$is_disabled = isset( $attributes['isDisabled'] ) && $attributes['isDisabled'] ? 'disabled' : '';

?>

<div class="<?php echo esc_attr( "{$block_class}" ); ?>">
  <?php
    $this->render_block_view(
      '/components/label/label.php',
      array(
        'blockClass' => $attributes['blockClass'] ?? '',
        'label'      => $attributes['label'] ?? '',
      )
    );
    ?>
  <div class="<?php echo esc_attr( "{$block_class}__content-wrap" ); ?>">
    <select
      <?php ! empty( $select_id ) ? printf( 'id="%s"', esc_attr( $select_id ) ) : ''; ?>
      name="<?php echo esc_attr( $name ); ?>"
      <?php echo esc_attr( $is_disabled ); ?>
      class="<?php echo esc_attr( "{$block_class}__select {$classes}" ); ?>"
    >
      <?php echo wp_kses_post( $inner_block_content ); ?>
    </select>
  </div>
</div>
