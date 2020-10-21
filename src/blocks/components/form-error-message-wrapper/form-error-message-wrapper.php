<?php
/**
 * Template for the Error message wrapper.
 *
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

use Eightshift_Libs\Helpers\Components;

$block_class     = $attributes['blockClass'] ?? '';
$component_class = 'form-error-message-wrapper';

$block_classes = Components::classnames([
  $component_class,
  "js-{$component_class}",
  'hide-form-message',
  ! empty ( $block_class ) ? "{$block_class}__{$component_class}" : '',
]);

?>
<div class="<?php echo esc_attr( $block_classes ); ?>"></div>
