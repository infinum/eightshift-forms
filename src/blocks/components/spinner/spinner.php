<?php
/**
 * Template for the Label Component.
 * @package Eightshift_Forms\Blocks.
 */

namespace Eightshift_Forms\Blocks;

use Eightshift_Libs\Helpers\Components;

$block_class = $attributes['blockClass'] ?? '';
$form_id = $attributes['formId'] ?? '';

$component_class = 'spinner';

$component_classes = Components::classnames([
  $component_class,
  "js-{$component_class}",
  "{$block_class}__{$component_class}",
  'hide-spinner'
]);

?>

<div
  class="<?php echo esc_attr( $component_classes ); ?>"
  role="alert"
  aria-live="assertive"
  data-parent-form="<?php echo esc_attr( $form_id ); ?>"
>
</div>
