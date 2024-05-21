<?php

/**
 * Template for the Checkbox Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$componentName = $manifest['componentName'] ?? '';
$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$checkboxValue = Helpers::checkAttr('checkboxValue', $attributes, $manifest);
if (!$checkboxValue) {
	return;
}

$checkboxLabel = Helpers::checkAttr('checkboxLabel', $attributes, $manifest);
$checkboxName = Helpers::checkAttr('checkboxName', $attributes, $manifest);
$checkboxUncheckedValue = Helpers::checkAttr('checkboxUncheckedValue', $attributes, $manifest);
$checkboxIsChecked = Helpers::checkAttr('checkboxIsChecked', $attributes, $manifest);
$checkboxIsDisabled = Helpers::checkAttr('checkboxIsDisabled', $attributes, $manifest);
$checkboxIsReadOnly = Helpers::checkAttr('checkboxIsReadOnly', $attributes, $manifest);
$checkboxTracking = Helpers::checkAttr('checkboxTracking', $attributes, $manifest);
$checkboxSingleSubmit = Helpers::checkAttr('checkboxSingleSubmit', $attributes, $manifest);
$checkboxAttrs = Helpers::checkAttr('checkboxAttrs', $attributes, $manifest);
$checkboxAsToggle = Helpers::checkAttr('checkboxAsToggle', $attributes, $manifest);
$checkboxAsToggleSize = Helpers::checkAttr('checkboxAsToggleSize', $attributes, $manifest);
$checkboxHideLabelText = Helpers::checkAttr('checkboxHideLabelText', $attributes, $manifest);
$checkboxHideLabel = Helpers::checkAttr('checkboxHideLabel', $attributes, $manifest);
$checkboxHelp = Helpers::checkAttr('checkboxHelp', $attributes, $manifest);
$checkboxFieldAttrs = Helpers::checkAttr('checkboxFieldAttrs', $attributes, $manifest);
$checkboxIcon = Helpers::checkAttr('checkboxIcon', $attributes, $manifest);
$checkboxIsHidden = Helpers::checkAttr('checkboxIsHidden', $attributes, $manifest);

if ($checkboxAsToggle) {
	$componentClass = "{$componentClass}-toggle";
}

$checkboxClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($componentClass && $checkboxAsToggleSize, $componentClass, '', $checkboxAsToggleSize),
	Helpers::selector($additionalClass, $additionalClass),
	Helpers::selector($checkboxIsDisabled, UtilsHelper::getStateSelector('isDisabled')),
	Helpers::selector($checkboxIsHidden, UtilsHelper::getStateSelector('isHidden')),
]);

$checkboxInputClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass, 'input'),
	Helpers::selector($checkboxSingleSubmit, UtilsHelper::getStateSelectorAdmin('singleSubmit')),
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

$conditionalTags = Helpers::render(
	'conditional-tags',
	Helpers::props('conditionalTags', $attributes)
);

if ($conditionalTags) {
	$checkboxFieldAttrs[UtilsHelper::getStateAttribute('conditionalTags')] = $conditionalTags;
}

$checkboxFieldAttrs[UtilsHelper::getStateAttribute('fieldName')] = $checkboxValue;

if ($componentName) {
	$checkboxFieldAttrs[UtilsHelper::getStateAttribute('fieldType')] = 'checkbox';
}

if ($checkboxTracking) {
	$checkboxFieldAttrs[UtilsHelper::getStateAttribute('tracking')] = esc_attr($checkboxTracking);
}

$checkboxFieldAttrsOutput = '';
if ($checkboxFieldAttrs) {
	foreach ($checkboxFieldAttrs as $key => $value) {
		$checkboxFieldAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

?>

<div class="<?php echo esc_attr($checkboxClass); ?>" <?php echo $checkboxFieldAttrsOutput; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>>
	<div class="<?php echo esc_attr("{$componentClass}__content"); ?>">
		<input
			class="<?php echo esc_attr($checkboxInputClass); ?>"
			type="checkbox"
			name="<?php echo esc_attr($checkboxName); ?>"
			id="<?php echo esc_attr($checkboxName); ?>"
			<?php echo $checkboxAttrsOutput; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>
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
			<?php echo $checkboxHelp; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>
		</div>
	<?php } ?>
</div>
