<?php

/**
 * Template for the Checkbox Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentName = $manifest['componentName'] ?? '';
$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;
$componentJsSingleSubmitClass = $manifest['componentJsSingleSubmitClass'] ?? '';

$checkboxValue = Components::checkAttr('checkboxValue', $attributes, $manifest);
if (!$checkboxValue) {
	return;
}

$checkboxLabel = Components::checkAttr('checkboxLabel', $attributes, $manifest);
$checkboxName = Components::checkAttr('checkboxName', $attributes, $manifest);
$checkboxUncheckedValue = Components::checkAttr('checkboxUncheckedValue', $attributes, $manifest);
$checkboxIsChecked = Components::checkAttr('checkboxIsChecked', $attributes, $manifest);
$checkboxIsDisabled = Components::checkAttr('checkboxIsDisabled', $attributes, $manifest);
$checkboxIsReadOnly = Components::checkAttr('checkboxIsReadOnly', $attributes, $manifest);
$checkboxTracking = Components::checkAttr('checkboxTracking', $attributes, $manifest);
$checkboxSingleSubmit = Components::checkAttr('checkboxSingleSubmit', $attributes, $manifest);
$checkboxAttrs = Components::checkAttr('checkboxAttrs', $attributes, $manifest);
$checkboxAsToggle = Components::checkAttr('checkboxAsToggle', $attributes, $manifest);
$checkboxAsToggleSize = Components::checkAttr('checkboxAsToggleSize', $attributes, $manifest);
$checkboxHideLabelText = Components::checkAttr('checkboxHideLabelText', $attributes, $manifest);
$checkboxHideLabel = Components::checkAttr('checkboxHideLabel', $attributes, $manifest);
$checkboxHelp = Components::checkAttr('checkboxHelp', $attributes, $manifest);
$checkboxFieldAttrs = Components::checkAttr('checkboxFieldAttrs', $attributes, $manifest);
$checkboxIcon = Components::checkAttr('checkboxIcon', $attributes, $manifest);
$checkboxIsHidden = Components::checkAttr('checkboxIsHidden', $attributes, $manifest);

if ($checkboxAsToggle) {
	$componentClass = "{$componentClass}-toggle";
}

$checkboxClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($componentClass && $checkboxAsToggleSize, $componentClass, '', $checkboxAsToggleSize),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($checkboxIsDisabled, 'es-form-is-disabled'),
	Components::selector($checkboxIsHidden, 'es-form-is-hidden'),
]);

$checkboxInputClass = Components::classnames([
	Components::selector($componentClass, $componentClass, 'input'),
	Components::selector($checkboxSingleSubmit, $componentJsSingleSubmitClass),
]);

$checkboxAttrs['value'] = esc_attr($checkboxValue);

$checkboxAttrsOutput = '';
if ($checkboxAttrs) {
	foreach ($checkboxAttrs as $key => $value) {
		$checkboxAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

// strlen used because we can have 0 as string.
if (strlen($checkboxUncheckedValue) !== 0) {
	$checkboxAttrsOutput .= wp_kses_post(" data-unchecked-value='" . $checkboxUncheckedValue . "'");
}

$conditionalTags = Components::render(
	'conditional-tags',
	Components::props('conditionalTags', $attributes)
);

if ($conditionalTags) {
	$checkboxFieldAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['conditionalTags']] = $conditionalTags;
}

$checkboxFieldAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['fieldName']] = $checkboxValue;

if ($componentName) {
	$checkboxFieldAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['fieldType']] = $componentName;
}

if ($checkboxTracking) {
	$checkboxFieldAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['tracking']] = esc_attr($checkboxTracking);
}

$checkboxFieldAttrsOutput = '';
if ($checkboxFieldAttrs) {
	foreach ($checkboxFieldAttrs as $key => $value) {
		$checkboxFieldAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

?>

<div class="<?php echo esc_attr($checkboxClass); ?>" <?php echo $checkboxFieldAttrsOutput; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>>
	<div class="<?php echo esc_attr("{$componentClass}__content"); ?>">
		<input
			class="<?php echo esc_attr($checkboxInputClass); ?>"
			type="checkbox"
			name="<?php echo esc_attr($checkboxName); ?>"
			id="<?php echo esc_attr($checkboxName); ?>"
			<?php echo $checkboxAttrsOutput; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
			<?php checked($checkboxIsChecked); ?>
			<?php disabled($checkboxIsDisabled); ?>
			<?php wp_readonly($checkboxIsReadOnly); ?>
		/>
		<?php if (!$checkboxHideLabel) { ?>
			<label
				for="<?php echo esc_attr($checkboxName); ?>"
				class="<?php echo esc_attr("{$componentClass}__label"); ?>"
			>
				<?php if ($checkboxIcon) { ?>
					<img  class="<?php echo esc_attr("{$componentClass}__label-icon"); ?>" src="<?php echo esc_url($checkboxIcon); ?>" alt="<?php echo esc_attr($checkboxLabel); ?>" />
				<?php } ?>

				<?php if (!$checkboxHideLabelText) { ?>
					<span class="<?php echo esc_attr("{$componentClass}__label-inner"); ?>">
						<?php echo wp_kses_post(apply_filters('the_content', $checkboxLabel)); ?>
					</span>
				<?php } ?>
			</label>
		<?php } ?>
	</div>
	<?php if ($checkboxHelp) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__help"); ?>">
			<?php echo $checkboxHelp; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
		</div>
	<?php } ?>
</div>
