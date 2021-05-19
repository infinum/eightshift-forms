<?php

/**
 * Template for the Textarea Block view.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

use EightshiftForms\Helpers\Components;

$blockClass = $attributes['blockClass'] ?? '';
$name = $attributes['name'] ?? '';
$value = $attributes['value'] ?? '';
$textareaId = $attributes['id'] ?? '';
$placeholder = $attributes['placeholder'] ?? '';
$classes = $attributes['classes'] ?? '';
$rows = $attributes['rows'] ?? '';
$cols = $attributes['cols'] ?? '';
$theme = $attributes['theme'] ?? '';
$isRequired = isset($attributes['isRequired']) && $attributes['isRequired'] ? 'required' : '';
$isDisabled = isset($attributes['isDisabled']) && $attributes['isDisabled'] ? 'disabled' : '';
$isReadOnly = isset($attributes['isReadOnly']) && $attributes['isReadOnly'] ? 'readonly' : '';
$preventSending = isset($attributes['preventSending']) && $attributes['preventSending'] ? 'data-do-not-send' : '';

$blockClasses = Components::classnames([
	$blockClass,
	! empty($theme) ? "{$blockClass}__theme--{$theme}" : '',
	! empty($isRequired) ? "{$blockClass}--is-required" : '',
	! empty($isDisabled) ? "{$blockClass}--is-disabled" : '',
	! empty($isReadOnly) ? "{$blockClass}--is-read-only" : '',
]);

if (empty($this)) {
	return;
}

?>

<div class="<?php echo esc_attr($blockClasses); ?>">
	<?php
	echo Components::render('label', [
		'blockClass' => $attributes['blockClass'] ?? '',
		'label' => $attributes['label'] ?? '',
		'id' => $attributes['id'] ?? '',
	]);
	?>
	<div class="<?php echo esc_attr("{$blockClass}__content-wrap"); ?>">
	<textarea
		name="<?php echo esc_attr($name); ?>"
		placeholder="<?php echo esc_attr($placeholder); ?>"
		<?php ! empty($textareaId) ? printf('id="%s"', esc_attr($textareaId)) : ''; // phpcs:ignore Eightshift.Security.CustomEscapeOutput.OutputNotEscaped ?>
		class="<?php echo esc_attr("{$classes} {$blockClass}__textarea"); ?>"
		value="<?php echo esc_attr($value); ?>"
		rows="<?php echo esc_attr($rows); ?>"
		cols="<?php echo esc_attr($cols); ?>"
		<?php echo esc_attr($isRequired); ?>
		<?php echo esc_attr($isDisabled); ?>
		<?php echo esc_attr($isReadOnly); ?>
		<?php echo esc_attr($preventSending); ?>
	></textarea>
	</div>
</div>
