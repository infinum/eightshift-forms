<?php

/**
 * Template for the Label Component.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

use EightshiftLibs\Helpers\Components;

$blockClass = $attributes['blockClass'] ?? '';
$theme = $attributes['theme'] ?? '';

$componentClass = 'form-spinner';

$componentClasses = Components::classnames([
  $componentClass,
  "js-{$componentClass}",
  'hide-spinner',
  ! empty($blockClass) ? "{$blockClass}__{$componentClass}" : '',
  ! empty($theme) ? "{$componentClass}__theme--{$theme}" : '',
]);

?>

<div
  class="<?php echo esc_attr($componentClasses); ?>"
  role="alert"
  aria-live="assertive"
>
</div>
