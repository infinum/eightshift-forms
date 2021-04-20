<?php
/**
 * Template for the Error message wrapper.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

use EightshiftLibs\Helpers\Components;

$block_class     = $attributes['blockClass'] ?? '';
$theme           = $attributes['theme'] ?? '';
$component_class = 'form-error-message-wrapper';

$block_classes = Components::classnames([
  $component_class,
  "js-{$component_class}",
  'is-form-message-hidden',
  ! empty( $block_class ) ? "{$block_class}__{$component_class}" : '',
  ! empty( $theme ) ? "{$component_class}__theme--{$theme}" : '',
]);

?>
<div class="<?php echo esc_attr( $block_classes ); ?>"></div>
