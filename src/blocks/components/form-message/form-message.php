<?php
/**
 * Template for the Form success / error message component.
 *
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

use Eightshift_Libs\Helpers\Components;

$block_class  = $attributes['blockClass'] ?? '';
$message      = $attributes['message'] ?? '';
$message_type = $attributes['type'] ?? 'success';

$component_class = 'form-message';

$block_classes = Components::classnames([
  $component_class,
  "js-{$component_class}",
  "js-{$component_class}--{$message_type}",
  "{$block_class}__type--{$message_type}",
  'is-form-message-hidden',
  ! empty( $block_class ) ? "{$block_class}__{$component_class}" : '',
]);

?>

<div class="<?php echo esc_attr( $block_classes ); ?>">
  <?php echo esc_html( $message ); ?>
</div>
