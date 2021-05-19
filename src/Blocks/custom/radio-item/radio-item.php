<?php

/**
 * Template for the Radio Item Block view.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

use EightshiftLibs\Helpers\Components;

$blockClass = $attributes['blockClass'] ?? '';
$label = $attributes['label'] ?? '';
$name = $attributes['name'] ?? '';
$value = $attributes['value'] ?? '';
$radioItemId = $attributes['id'] ?? '';
$classes = $attributes['classes'] ?? '';
$theme = $attributes['theme'] ?? '';
$isChecked = isset($attributes['isChecked']) && $attributes['isChecked'] ? 'checked' : '';
$isDisabled = isset($attributes['isDisabled']) && $attributes['isDisabled'] ? 'disabled' : '';
$isReadOnly = isset($attributes['isReadOnly']) && $attributes['isReadOnly'] ? 'readonly' : '';

$blockClasses = Components::classnames([
	$blockClass,
	! empty($theme) ? "{$blockClass}__theme--{$theme}" : '',
]);

?>

<div class="<?php echo esc_attr($blockClasses); ?>">
	<label class="<?php echo esc_attr("{$blockClass}__label"); ?>">
	<input
		name="<?php echo esc_attr($name); ?>"
		<?php ! empty($radioItemId) ? printf('id="%s"', esc_attr($radioItemId)) : ''; // phpcs:ignore Eightshift.Security.CustomEscapeOutput.OutputNotEscaped ?>
		class="<?php echo esc_attr("{$classes} {$blockClass}__radio"); ?>"
		value="<?php echo esc_attr($value); ?>"
		type="radio"
		<?php echo esc_attr($isChecked); ?>
		<?php echo esc_attr($isDisabled); ?>
		<?php echo esc_attr($isReadOnly); ?>
	/>
	<span class="<?php echo esc_attr("{$blockClass}__radio-icon js-{$blockClass}-radio-icon"); ?>"></span>
	<span class="<?php echo esc_attr("{$blockClass}__label-content"); ?>">
		<?php echo wp_kses_post($label); ?>
	</span>
	</label>
</div>
