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
$componentJsSingleSubmitClass = $manifest['componentJsSingleSubmitClass'] ?? '';

$radioLabel = Components::checkAttr('radioLabel', $attributes, $manifest);
$radioId = Components::checkAttr('radioId', $attributes, $manifest);
$radioName = Components::checkAttr('radioName', $attributes, $manifest);
$radioValue = Components::checkAttr('radioValue', $attributes, $manifest);
$radioIsChecked = Components::checkAttr('radioIsChecked', $attributes, $manifest);
$radioIsDisabled = Components::checkAttr('radioIsDisabled', $attributes, $manifest);
$radioTracking = Components::checkAttr('radioTracking', $attributes, $manifest);
$radioSingleSubmit = Components::checkAttr('radioSingleSubmit', $attributes, $manifest);

$radioClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($radioIsDisabled, $componentClass, '', 'disabled'),
]);

$radioInputClass = Components::classnames([
	Components::selector($componentClass, $componentClass, 'input'),
	Components::selector($radioSingleSubmit, $componentJsSingleSubmitClass),
]);

if (empty($radioLabel)) {
	return;
}

?>

<div class="<?php echo esc_attr($radioClass); ?>">
	<div class="<?php echo esc_attr("{$componentClass}__content"); ?>">
		<input
			class="<?php echo esc_attr($radioInputClass); ?>"
			type="radio"
			name="<?php echo esc_attr($radioName); ?>"
			id="<?php echo esc_attr($radioId); ?>"
			value="<?php echo esc_attr($radioValue); ?>"
			data-tracking="<?php echo esc_attr($radioTracking); ?>"
			<?php checked($radioIsChecked); ?>
			<?php disabled($radioIsDisabled); ?>
		/>
		<label
			for="<?php echo esc_attr($radioId); ?>"
			class="<?php echo esc_attr("{$componentClass}__label"); ?>"
		>
			<?php echo wp_kses_post(\apply_filters('the_content', $radioLabel)); ?>
		</label>
	</div>
</div>
