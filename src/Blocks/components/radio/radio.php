<?php

/**
 * Template for the radio Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$radioLabel = Components::checkAttr('radioLabel', $attributes, $manifest);
$radioId = Components::checkAttr('radioId', $attributes, $manifest);
$radioName = Components::checkAttr('radioName', $attributes, $manifest);
$radioValue = Components::checkAttr('radioValue', $attributes, $manifest);
$radioIsChecked = Components::checkAttr('radioIsChecked', $attributes, $manifest);
$radioIsDisabled = Components::checkAttr('radioIsDisabled', $attributes, $manifest);
$radioIsReadOnly = Components::checkAttr('radioIsReadOnly', $attributes, $manifest);
$radioIsRequired = Components::checkAttr('radioIsRequired', $attributes, $manifest);
$radioTracking = Components::checkAttr('radioTracking', $attributes, $manifest);

$radioClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
]);

?>

<div class="<?php echo esc_attr($radioClass); ?>">
	<div class="<?php echo esc_attr("{$componentClass}__content"); ?>">
		<input
			class="<?php echo esc_attr("{$componentClass}__input"); ?>"
			type="radio"
			name="<?php echo esc_attr($radioName); ?>"
			id="<?php echo esc_attr($radioId); ?>"
			value="<?php echo esc_attr($radioValue); ?>"
			data-validation-required="<?php echo esc_attr($radioIsRequired); ?>"
			data-tracking="<?php echo esc_attr($radioTracking); ?>"
			<?php echo $radioIsChecked ? 'checked' : ''; ?>
			<?php echo $radioIsDisabled ? 'disabled' : ''; ?>
			<?php echo $radioIsReadOnly ? 'readonly' : ''; ?>
		/>
		<label
			for="<?php echo esc_attr($radioId); ?>"
			class="<?php echo esc_attr("{$componentClass}__label"); ?>"
		>
			<?php echo wp_kses_post(\apply_filters('the_content', $radioLabel)); ?>
		</label>
	</div>
</div>
