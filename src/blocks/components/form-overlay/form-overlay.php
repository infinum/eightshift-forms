<?php
/**
 * Template for the form overlay component. Used for preventing any clicks on form elements while form is submitting.
 *
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

use Eightshift_Libs\Helpers\Components;

$block_class  = $attributes['blockClass'] ?? '';

$component_class = 'form-overlay';

$block_classes = Components::classnames([
  $component_class,
  "js-{$component_class}",
  "hide-form-overlay",
  ! empty ( $block_class ) ? "{$block_class}__{$component_class}" : '',
]);

?>

<div class="<?php echo esc_attr( $block_classes ); ?>" aria-hidden="true"></div>
