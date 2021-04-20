<?php

/**
 * Template for the Radio Item Block view.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

use EightshiftLibs\Helpers\Components;

$block_class   = $attributes['blockClass'] ?? '';
$label         = $attributes['label'] ?? '';
$name          = $attributes['name'] ?? '';
$value         = $attributes['value'] ?? '';
$radio_item_id = $attributes['id'] ?? '';
$classes       = $attributes['classes'] ?? '';
$theme         = $attributes['theme'] ?? '';
$is_checked    = isset($attributes['isChecked']) && $attributes['isChecked'] ? 'checked' : '';
$is_disabled   = isset($attributes['isDisabled']) && $attributes['isDisabled'] ? 'disabled' : '';
$is_read_only  = isset($attributes['isReadOnly']) && $attributes['isReadOnly'] ? 'readonly' : '';

$block_classes = Components::classnames([
  $block_class,
  ! empty($theme) ? "{$block_class}__theme--{$theme}" : '',
]);

?>

<div class="<?php echo esc_attr($block_classes); ?>">
  <label class="<?php echo esc_attr("{$block_class}__label"); ?>">
	<input
	  name="<?php echo esc_attr($name); ?>"
	  <?php ! empty($radio_item_id) ? printf('id="%s"', esc_attr($radio_item_id)) : ''; ?>
	  class="<?php echo esc_attr("{$classes} {$block_class}__radio"); ?>"
	  value="<?php echo esc_attr($value); ?>"
	  type="radio"
	  <?php echo esc_attr($is_checked); ?>
	  <?php echo esc_attr($is_disabled); ?>
	  <?php echo esc_attr($is_read_only); ?>
	/>
	<span class="<?php echo esc_attr("{$block_class}__radio-icon js-{$block_class}-radio-icon"); ?>"></span>
	<span class="<?php echo esc_attr("{$block_class}__label-content"); ?>">
	  <?php echo wp_kses_post($label); ?>
	</span>
  </label>
</div>
