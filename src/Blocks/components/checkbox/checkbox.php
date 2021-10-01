<?php

/**
 * Template for the Checkbox Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$checkboxLabel = Components::checkAttr('checkboxLabel', $attributes, $manifest);
$checkboxId = Components::checkAttr('checkboxId', $attributes, $manifest);
$checkboxName = Components::checkAttr('checkboxName', $attributes, $manifest);
$checkboxValue = Components::checkAttr('checkboxValue', $attributes, $manifest);
$checkboxIsChecked = Components::checkAttr('checkboxIsChecked', $attributes, $manifest);
$checkboxIsDisabled = Components::checkAttr('checkboxIsDisabled', $attributes, $manifest);
$checkboxIsReadOnly = Components::checkAttr('checkboxIsReadOnly', $attributes, $manifest);
$checkboxIsRequired = Components::checkAttr('checkboxIsRequired', $attributes, $manifest);
$checkboxTracking = Components::checkAttr('checkboxTracking', $attributes, $manifest);

$checkboxClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($checkboxIsDisabled, $componentClass, '', 'disabled'),
]);

?>

<div class="<?php echo esc_attr($checkboxClass); ?>">
	<div class="<?php echo esc_attr("{$componentClass}__content"); ?>">
		<input
			class="<?php echo esc_attr("{$componentClass}__input"); ?>"
			type="checkbox"
			name="<?php echo esc_attr($checkboxName); ?>"
			id="<?php echo esc_attr($checkboxId); ?>"
			value="<?php echo esc_attr($checkboxValue); ?>"
			data-validation-required="<?php echo esc_attr($checkboxIsRequired); ?>"
			data-tracking="<?php echo esc_attr($checkboxTracking); ?>"
			<?php echo $checkboxIsChecked ? 'checked' : ''; ?>
			<?php echo $checkboxIsDisabled ? 'disabled' : ''; ?>
			<?php echo $checkboxIsReadOnly ? 'readonly' : ''; ?>
		/>
		<label
			for="<?php echo esc_attr($checkboxId); ?>"
			class="<?php echo esc_attr("{$componentClass}__label"); ?>"
		>
			<?php echo wp_kses_post(\apply_filters('the_content', $checkboxLabel)); ?>
		</label>
	</div>

	<?php
	echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'error',
		Components::props('error', $attributes, [
			'errorId' => $checkboxName,
			'blockClass' => $componentClass
		])
	);
	?>
</div>
