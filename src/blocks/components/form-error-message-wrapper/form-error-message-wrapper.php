<?php

/**
 * Template for the Error message wrapper.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

use EightshiftLibs\Helpers\Components;

$blockClass = $attributes['blockClass'] ?? '';
$theme = $attributes['theme'] ?? '';
$componentClass = 'form-error-message-wrapper';

$blockClasses = Components::classnames([
  $componentClass,
  "js-{$componentClass}",
  'is-form-message-hidden',
  ! empty($blockClass) ? "{$blockClass}__{$componentClass}" : '',
  ! empty($theme) ? "{$componentClass}__theme--{$theme}" : '',
]);

?>
<div class="<?php echo esc_attr($blockClasses); ?>"></div>
