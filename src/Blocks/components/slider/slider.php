<?php

/**
 * Template for the Slider Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$sliderName = Components::checkAttr('sliderName', $attributes, $manifest);
if (!$sliderName) {
	return;
}

$sliderValue = Components::checkAttr('sliderValue', $attributes, $manifest);
$sliderTypeCustom = Components::checkAttr('sliderTypeCustom', $attributes, $manifest);
$sliderIsDisabled = Components::checkAttr('sliderIsDisabled', $attributes, $manifest);
$sliderIsReadOnly = Components::checkAttr('sliderIsReadOnly', $attributes, $manifest);
$sliderIsRequired = Components::checkAttr('sliderIsRequired', $attributes, $manifest);
$sliderTracking = Components::checkAttr('sliderTracking', $attributes, $manifest);
$sliderAttrs = Components::checkAttr('sliderAttrs', $attributes, $manifest);
$sliderFieldAttrs = Components::checkAttr('sliderFieldAttrs', $attributes, $manifest);
$sliderStartAmount = Components::checkAttr('sliderStartAmount', $attributes, $manifest);
$sliderStepAmount = Components::checkAttr('sliderStepAmount', $attributes, $manifest);
$sliderEndAmount = Components::checkAttr('sliderEndAmount', $attributes, $manifest);
$sliderHideLabel = false;

$sliderClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	UtilsHelper::getStateSelector('slider'),
]);

$sliderAttrs[UtilsHelper::getStateAttribute('sliderValue')] = $sliderValue;

$sliderAttrsOutput = '';
if ($sliderAttrs) {
	foreach ($sliderAttrs as $key => $value) {
		$sliderAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

// Additional content filter.
$additionalContent = UtilsGeneralHelper::getBlockAdditionalContentViaFilter('slider', $attributes);

$stars = '';

// for ($i = 1; $i < $sliderAmount + 1; $i++) {
// 	$stars .= '
// 		<div
// 			class="' . esc_attr("{$componentClass}__star") . '"
// 			' . UtilsHelper::getStateAttribute('sliderValue') . '="' . $i . '"
// 		>
// 		' . UtilsHelper::getUtilsIcons('slider') .
// 		'</div>';
// }

$slider = '
	<input
		class="' . esc_attr($componentClass) . '__input"
		name="' . esc_attr($sliderName) . '"
		id="' . esc_attr($sliderName) . '"
		value="' . esc_attr($sliderValue) . '"
		type="text"
		' . disabled($sliderIsDisabled, true, false) . '
		' . wp_readonly($sliderIsReadOnly, true, false) . '
	/>
	<div
	class="' . esc_attr($sliderClass) . '"
		' . $sliderAttrsOutput . '
	>
	' . $stars . '
	</div>
	' . $additionalContent . '
';

echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $slider,
			'fieldId' => $sliderName,
			'fieldName' => $sliderName,
			'fieldTypeInternal' => FormsHelper::getStateFieldType('slider'),
			'fieldIsRequired' => $sliderIsRequired,
			'fieldDisabled' => !empty($sliderIsDisabled),
			'fieldTypeCustom' => $sliderTypeCustom ?: 'slider', // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
			'fieldTracking' => $sliderTracking,
			'fieldHideLabel' => $sliderHideLabel,
			'fieldConditionalTags' => Components::render(
				'conditional-tags',
				Components::props('conditionalTags', $attributes)
			),
			'fieldAttrs' => $sliderFieldAttrs,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? ''
		]
	)
);
