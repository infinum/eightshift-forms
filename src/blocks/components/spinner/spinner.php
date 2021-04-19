<?php
/**
 * Template for the Label Component.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

use Eightshift_Libs\Helpers\Components;

$block_class = $attributes['blockClass'] ?? '';
$theme       = $attributes['theme'] ?? '';

$component_class = 'form-spinner';

$component_classes = Components::classnames([
  $component_class,
  "js-{$component_class}",
  'hide-spinner',
  ! empty( $block_class ) ? "{$block_class}__{$component_class}" : '',
  ! empty( $theme ) ? "{$component_class}__theme--{$theme}" : '',
]);

?>

<div
  class="<?php echo esc_attr( $component_classes ); ?>"
  role="alert"
  aria-live="assertive"
>
</div>
