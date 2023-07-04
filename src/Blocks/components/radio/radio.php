<?php

/**
 * Template for the radio Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Rest\Routes\AbstractBaseRoute;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentName = $manifest['componentName'] ?? '';
$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$componentJsSingleSubmitClass = $manifest['componentJsSingleSubmitClass'] ?? '';

$radioValue = Components::checkAttr('radioValue', $attributes, $manifest);
if (!$radioValue) {
	return;
}

$radioLabel = Components::checkAttr('radioLabel', $attributes, $manifest);
$radioName = Components::checkAttr('radioName', $attributes, $manifest);
$radioIsChecked = Components::checkAttr('radioIsChecked', $attributes, $manifest);
$radioIsDisabled = Components::checkAttr('radioIsDisabled', $attributes, $manifest);
$radioSingleSubmit = Components::checkAttr('radioSingleSubmit', $attributes, $manifest);
$radioAttrs = Components::checkAttr('radioAttrs', $attributes, $manifest);
$radioFieldAttrs = Components::checkAttr('radioFieldAttrs', $attributes, $manifest);
$radioIcon = Components::checkAttr('radioIcon', $attributes, $manifest);

$radioClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($radioIsDisabled, $componentClass, '', 'disabled'),
]);

$radioInputClass = Components::classnames([
	Components::selector($componentClass, $componentClass, 'input'),
	Components::selector($radioSingleSubmit, $componentJsSingleSubmitClass),
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
	$radioFieldAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['conditionalTags']] = $conditionalTags;
}

$radioFieldAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['fieldName']] = $radioValue;

if ($componentName) {
	$radioFieldAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['fieldType']] = $componentName;
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
		<label
			for="<?php echo esc_attr($radioName); ?>"
			class="<?php echo esc_attr("{$componentClass}__label"); ?>"
		>
			<span class="<?php echo esc_attr("{$componentClass}__label-inner"); ?>">
				<?php echo wp_kses_post(apply_filters('the_content', $radioLabel)); ?>
			</span>

			<?php if ($radioIcon) { ?>
				<img  class="<?php echo esc_attr("{$componentClass}__label-icon"); ?>" src="<?php echo esc_url($radioIcon); ?>" alt="<?php echo esc_attr($radioLabel); ?>" />
			<?php } ?>
		</label>
	</div>
</div>
