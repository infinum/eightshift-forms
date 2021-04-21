<?php

/**
 * Template for the Checkbox Block view.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

use EightshiftLibs\Helpers\Components;

$blockClass = $attributes['blockClass'] ?? '';
$name = $attributes['name'] ?? '';
$value = $attributes['value'] ?? '';
$label = $attributes['label'] ?? '';
$checkboxId = $attributes['id'] ?? '';
$classes = $attributes['classes'] ?? '';
$theme = $attributes['theme'] ?? '';
$isChecked = isset($attributes['isChecked']) && $attributes['isChecked'] ? 'checked' : '';
$isDisabled = isset($attributes['isDisabled']) && $attributes['isDisabled'] ? 'disabled' : '';
$isReadOnly = isset($attributes['isReadOnly']) && $attributes['isReadOnly'] ? 'readonly' : '';
$isRequired = isset($attributes['isRequired']) && $attributes['isRequired'] ? 'required' : '';
$preventSending = isset($attributes['preventSending']) && $attributes['preventSending'] ? 'data-do-not-send' : '';

$blockClasses = Components::classnames([
  $blockClass,
  ! empty($theme) ? "{$blockClass}__theme--{$theme}" : '',
])
?>

<div class="<?php echo esc_attr($blockClasses); ?>">
  <label class="<?php echo esc_attr("{$blockClass}__label js-{$blockClass}-label"); ?>">
	<input
	  name="<?php echo esc_attr($name); ?>"
	  <?php ! empty($checkboxId) ? printf('id="%s"', esc_attr($checkboxId)) : ''; ?>
	  class="<?php echo esc_attr("{$classes} {$blockClass}__checkbox js-{$blockClass}-checkbox"); ?>"
	  value="<?php echo esc_attr($value); ?>"
	  type="checkbox"
	  <?php echo esc_attr($isChecked); ?>
	  <?php echo esc_attr($isDisabled); ?>
	  <?php echo esc_attr($isReadOnly); ?>
	  <?php echo esc_attr($isRequired); ?>
	  <?php echo esc_attr($preventSending); ?>
	/>
	<span class="<?php echo esc_attr("{$blockClass}__checkmark js-{$blockClass}-checkmark"); ?>"></span>
	<span class="<?php echo esc_attr("{$blockClass}__label-content"); ?>">
	  <?php echo wp_kses_post($label); ?>
	</span>
  </label>
</div>
