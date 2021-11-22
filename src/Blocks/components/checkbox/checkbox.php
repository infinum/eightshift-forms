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
$selectorClass = $attributes['selectorClass'] ?? $componentClass;
$componentJsSingleSubmitClass = $manifest['componentJsSingleSubmitClass'] ?? '';

$checkboxLabel = Components::checkAttr('checkboxLabel', $attributes, $manifest);
$checkboxId = Components::checkAttr('checkboxId', $attributes, $manifest);
$checkboxName = Components::checkAttr('checkboxName', $attributes, $manifest);
$checkboxValue = Components::checkAttr('checkboxValue', $attributes, $manifest);
$checkboxIsChecked = Components::checkAttr('checkboxIsChecked', $attributes, $manifest);
$checkboxIsDisabled = Components::checkAttr('checkboxIsDisabled', $attributes, $manifest);
$checkboxIsReadOnly = Components::checkAttr('checkboxIsReadOnly', $attributes, $manifest);
$checkboxTracking = Components::checkAttr('checkboxTracking', $attributes, $manifest);
$checkboxSingleSubmit = Components::checkAttr('checkboxSingleSubmit', $attributes, $manifest);

$checkboxClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($checkboxIsDisabled, $componentClass, '', 'disabled'),
]);

$checkboxInputClass = Components::classnames([
	Components::selector($componentClass, $componentClass, 'input'),
	Components::selector($checkboxSingleSubmit, $componentJsSingleSubmitClass),
]);

if (empty($checkboxLabel)) {
	return;
}

$attrsOutput = '';
if ($checkboxTracking) {
	$attrsOutput .= " data-tracking='" . $checkboxTracking . "'";
}

if ($checkboxValue) {
	$attrsOutput .= " value='" . $checkboxValue . "'";
}

?>

<div class="<?php echo esc_attr($checkboxClass); ?>">
	<div class="<?php echo esc_attr("{$componentClass}__content"); ?>">
		<input
			class="<?php echo esc_attr($checkboxInputClass); ?>"
			type="checkbox"
			name="<?php echo esc_attr($checkboxName); ?>"
			id="<?php echo esc_attr($checkboxId); ?>"
			<?php echo $attrsOutput; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php checked($checkboxIsChecked); ?>
			<?php disabled($checkboxIsDisabled); ?>
			<?php readonly($checkboxIsReadOnly); ?>
		/>
		<label
			for="<?php echo esc_attr($checkboxId); ?>"
			class="<?php echo esc_attr("{$componentClass}__label"); ?>"
		>
			<?php echo wp_kses_post(\apply_filters('the_content', $checkboxLabel)); ?>
		</label>
	</div>
</div>
