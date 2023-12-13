<?php

/**
 * Template for the radio Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Helper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentName = $manifest['componentName'] ?? '';
$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$radioValue = Components::checkAttr('radioValue', $attributes, $manifest);
if (!$radioValue) {
	return;
}

$radioLabel = Components::checkAttr('radioLabel', $attributes, $manifest);
$radioHideLabel = Components::checkAttr('radioHideLabel', $attributes, $manifest);
$radioHideLabelText = Components::checkAttr('radioHideLabelText', $attributes, $manifest);
$radioName = Components::checkAttr('radioName', $attributes, $manifest);
$radioIsChecked = Components::checkAttr('radioIsChecked', $attributes, $manifest);
$radioIsDisabled = Components::checkAttr('radioIsDisabled', $attributes, $manifest);
$radioSingleSubmit = Components::checkAttr('radioSingleSubmit', $attributes, $manifest);
$radioAttrs = Components::checkAttr('radioAttrs', $attributes, $manifest);
$radioFieldAttrs = Components::checkAttr('radioFieldAttrs', $attributes, $manifest);
$radioIcon = Components::checkAttr('radioIcon', $attributes, $manifest);
$radioIsHidden = Components::checkAttr('radioIsHidden', $attributes, $manifest);

$radioClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($radioIsDisabled, Helper::getStateSelector('isDisabled')),
	Components::selector($radioIsHidden, Helper::getStateSelector('isHidden')),
]);

$radioInputClass = Components::classnames([
	Components::selector($componentClass, $componentClass, 'input'),
	Components::selector($radioSingleSubmit, Helper::getStateSelectorAdmin('singleSubmit')),
]);

$radioAttrs['value'] = esc_attr($radioValue);

$radioAttrsOutput = '';
if ($radioAttrs) {
	foreach ($radioAttrs as $key => $value) {
		$radioAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

$conditionalTags = Components::render(
	'conditional-tags',
	Components::props('conditionalTags', $attributes)
);

if ($conditionalTags) {
	$radioFieldAttrs[Helper::getStateAttribute('conditionalTags')] = $conditionalTags;
}

$radioFieldAttrs[Helper::getStateAttribute('fieldName')] = $radioValue;

if ($componentName) {
	$radioFieldAttrs[Helper::getStateAttribute('fieldType')] = 'radio';
}

$radioFieldAttrsOutput = '';
if ($radioFieldAttrs) {
	foreach ($radioFieldAttrs as $key => $value) {
		$radioFieldAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

?>

<div class="<?php echo esc_attr($radioClass); ?>" <?php echo $radioFieldAttrsOutput; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>>
	<div class="<?php echo esc_attr("{$componentClass}__content"); ?>">
		<input
			class="<?php echo esc_attr($radioInputClass); ?>"
			type="radio"
			name="<?php echo esc_attr($radioName); ?>"
			id="<?php echo esc_attr($radioName); ?>"
			<?php checked($radioIsChecked); ?>
			<?php disabled($radioIsDisabled); ?>
			<?php echo $radioAttrsOutput; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
		/>
		<?php if (!$radioHideLabel) { ?>
			<label
				for="<?php echo esc_attr($radioName); ?>"
				class="<?php echo esc_attr("{$componentClass}__label"); ?>"
			>
				<?php if ($radioIcon) { ?>
					<img  class="<?php echo esc_attr("{$componentClass}__label-icon"); ?>" src="<?php echo esc_url($radioIcon); ?>" alt="<?php echo esc_attr($radioLabel); ?>" />
				<?php } ?>

				<?php if (!$radioHideLabelText) { ?>
					<span class="<?php echo esc_attr("{$componentClass}__label-inner"); ?>">
						<?php echo wp_kses_post(apply_filters('the_content', $radioLabel)); ?>
					</span>
				<?php } ?>
			</label>
		<?php } ?>
	</div>
</div>
