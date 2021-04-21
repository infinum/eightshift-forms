<?php

/**
 * Template for the form overlay component. Used for preventing any clicks on form elements while form is submitting.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

use EightshiftLibs\Helpers\Components;

$blockClass = $attributes['blockClass'] ?? '';

$componentClass = 'form-overlay';

$blockClasses = Components::classnames([
  $componentClass,
  "js-{$componentClass}",
  'hide-form-overlay',
  ! empty($blockClass) ? "{$blockClass}__{$componentClass}" : '',
]);

?>

<div class="<?php echo esc_attr($blockClasses); ?>" aria-hidden="true"></div>
