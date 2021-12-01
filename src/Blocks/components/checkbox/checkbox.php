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

$checkboxAttrs = [];
if ($checkboxTracking) {
	$checkboxAttrs['data-tracking'] = esc_attr($checkboxTracking);
}

if ($checkboxValue) {
	$checkboxAttrs['value'] = esc_attr($checkboxValue);
}

$checkboxAttrsOutput = '';
if ($checkboxAttrs) {
	foreach ($checkboxAttrs as $key => $value) {
		$checkboxAttrsOutput .= \wp_kses_post(" {$key}='" . $value . "'");
	}
}

?>

<div class="<?php echo esc_attr($checkboxClass); ?>">
	<div class="<?php echo esc_attr("{$componentClass}__content"); ?>">
		<input
			class="<?php echo esc_attr($checkboxInputClass); ?>"
			type="checkbox"
			name="<?php echo esc_attr($checkboxName); ?>"
			id="<?php echo esc_attr($checkboxId); ?>"
			<?php echo $checkboxAttrsOutput; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php checked($checkboxIsChecked); ?>
			<?php disabled($checkboxIsDisabled); ?>
			<?php readonly($checkboxIsReadOnly); ?>
		/>
		<label
			for="<?php echo esc_attr($checkboxId); ?>"
			class="<?php echo esc_attr("{$componentClass}__label"); ?>"
		>
			<span class="<?php echo esc_attr("{$componentClass}__label-inner"); ?>">
				<?php echo wp_kses_post(\apply_filters('the_content', $checkboxLabel)); ?>
			</span>
		</label>
	</div>
</div>
