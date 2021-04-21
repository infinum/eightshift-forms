<?php

/**
 * Template for the Submit Block view.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

use EightshiftLibs\Helpers\Components;

$blockClass = $attributes['blockClass'] ?? '';
$name = $attributes['name'] ?? '';
$value = $attributes['value'] ?? '';
$submitId = $attributes['id'] ?? '';
$classes = $attributes['classes'] ?? '';
$theme = $attributes['theme'] ?? '';
$submitType = isset($attributes['type']) ? $attributes['type'] : 'submit';
$isDisabled = isset($attributes['isDisabled']) && $attributes['isDisabled'] ? 'disabled' : '';

$blockClasses = Components::classnames([
  $blockClass,
  ! empty($theme) ? "{$blockClass}__theme--{$theme}" : '',
  ! empty($isDisabled) ? "{$blockClass}--is-disabled" : '',
]);
?>

<div class="<?php echo esc_attr($blockClasses); ?>">
  <?php if ($submitType === 'button') { ?>
	<button
	  name="<?php echo esc_attr($name); ?>"
		<?php ! empty($submitId) ? printf('id="%s"', esc_attr($submitId)) : ''; ?>
	  class="<?php echo esc_attr("{$classes} {$blockClass}__button"); ?>"
		<?php echo esc_attr($isDisabled); ?>
	>
		<?php echo esc_html($value); ?>
	</button>
  <?php } else { ?>
	<input
	  name="<?php echo esc_attr($name); ?>"
	  <?php ! empty($submitId) ? printf('id="%s"', esc_attr($submitId)) : ''; ?>
	  class="<?php echo esc_attr("{$classes} {$blockClass}__input"); ?>"
	  value="<?php echo esc_attr($value); ?>"
	  type="<?php echo esc_attr($submitType); ?>"
	  <?php echo esc_attr($isDisabled); ?>
	/>
  <?php } ?>
</div>
