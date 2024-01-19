<?php

/**
 * Template for the Input Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$inputName = Components::checkAttr('inputName', $attributes, $manifest);
if (!$inputName) {
	return;
}

$inputValue = Components::checkAttr('inputValue', $attributes, $manifest);
$inputPlaceholder = Components::checkAttr('inputPlaceholder', $attributes, $manifest);
$inputType = Components::checkAttr('inputType', $attributes, $manifest);
$inputTypeCustom = Components::checkAttr('inputTypeCustom', $attributes, $manifest);
$inputIsDisabled = Components::checkAttr('inputIsDisabled', $attributes, $manifest);
$inputIsReadOnly = Components::checkAttr('inputIsReadOnly', $attributes, $manifest);
$inputIsRequired = Components::checkAttr('inputIsRequired', $attributes, $manifest);
$inputTracking = Components::checkAttr('inputTracking', $attributes, $manifest);
$inputMin = Components::checkAttr('inputMin', $attributes, $manifest);
$inputMax = Components::checkAttr('inputMax', $attributes, $manifest);
$inputStep = Components::checkAttr('inputStep', $attributes, $manifest);
$inputAttrs = Components::checkAttr('inputAttrs', $attributes, $manifest);
$inputFieldAttrs = Components::checkAttr('inputFieldAttrs', $attributes, $manifest);
$inputUseLabelAsPlaceholder = Components::checkAttr('inputUseLabelAsPlaceholder', $attributes, $manifest);

// Fix for getting attribute that is part of the child component.
$inputHideLabel = false;
$inputFieldLabel = $attributes[Components::getAttrKey('inputFieldLabel', $attributes, $manifest)] ?? '';

$inputClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
]);

// Override types.
if ($inputType === 'email' || $inputType === 'url') {
	$inputType = 'text';
}

if ($inputValue) {
	$inputAttrs['value'] = esc_attr($inputValue);
}

if ($inputPlaceholder) {
	$inputAttrs['placeholder'] = esc_attr($inputPlaceholder);
}

if ($inputUseLabelAsPlaceholder) {
	$inputAttrs['placeholder'] = esc_attr($inputFieldLabel);
	$inputHideLabel = true;
}

$inputAttrsOutput = '';
if ($inputAttrs) {
	foreach ($inputAttrs as $key => $value) {
		$inputAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

// Additional content filter.
$additionalContent = UtilsGeneralHelper::getBlockAdditionalContentViaFilter('input', $attributes);

$input = '
	<input
		class="' . esc_attr($inputClass) . '"
		name="' . esc_attr($inputName) . '"
		id="' . esc_attr($inputName) . '"
		type="' . esc_attr($inputType) . '"
		' . disabled($inputIsDisabled, true, false) . '
		' . wp_readonly($inputIsReadOnly, true, false) . '
		' . $inputAttrsOutput . '
	/>
	' . $additionalContent . '
';

echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $input,
			'fieldId' => $inputName,
			'fieldName' => $inputName,
			'fieldTypeInternal' => FormsHelper::getStateFieldType('input'),
			'fieldIsRequired' => $inputIsRequired,
			'fieldDisabled' => !empty($inputIsDisabled),
			'fieldUseError' => $inputType !== 'hidden',
			'fieldTypeCustom' => $inputTypeCustom ?: $inputType, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
			'fieldTracking' => $inputTracking,
			'fieldHideLabel' => $inputHideLabel || $inputType === 'hidden',
			'fieldConditionalTags' => Components::render(
				'conditional-tags',
				Components::props('conditionalTags', $attributes)
			),
			'fieldAttrs' => $inputFieldAttrs,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? ''
		]
	)
);
