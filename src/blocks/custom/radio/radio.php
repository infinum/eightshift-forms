<?php
/**
 * Template for the Radio Block view.
 *
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

use Eightshift_Libs\Helpers\Components;

$block_class     = $attributes['blockClass'] ?? '';
$theme           = $attributes['theme'] ?? '';

$block_classes = Components::classnames([
  $block_class,
  ! empty( $theme ) ? "{$block_class}__theme--{$theme}" : '',
]);

?>

<div class="<?php echo esc_attr( $block_classes ); ?>">
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
    <?php echo wp_kses_post( $inner_block_content ); ?>
  </div>
</div>
