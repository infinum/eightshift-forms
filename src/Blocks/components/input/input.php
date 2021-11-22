<?php

/**
 * Template for the Input Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$inputId = Components::checkAttr('inputId', $attributes, $manifest);
$inputName = Components::checkAttr('inputName', $attributes, $manifest);
$inputValue = Components::checkAttr('inputValue', $attributes, $manifest);
$inputPlaceholder = Components::checkAttr('inputPlaceholder', $attributes, $manifest);
$inputType = Components::checkAttr('inputType', $attributes, $manifest);
$inputIsDisabled = Components::checkAttr('inputIsDisabled', $attributes, $manifest);
$inputIsReadOnly = Components::checkAttr('inputIsReadOnly', $attributes, $manifest);
$inputTracking = Components::checkAttr('inputTracking', $attributes, $manifest);
$inputMin = Components::checkAttr('inputMin', $attributes, $manifest);
$inputMax = Components::checkAttr('inputMax', $attributes, $manifest);
$inputStep = Components::checkAttr('inputStep', $attributes, $manifest);

// Fix for getting attribute that is part of the child component.
$inputFieldLabel = $attributes[Components::getAttrKey('inputFieldLabel', $attributes, $manifest)] ?? '';

$inputClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
]);

$attrsOutput = '';
if ($inputType === 'number') {
	if ($inputMin || $inputMin === 0) {
		$attrsOutput .= " min='" . esc_attr($inputMin) . "'";
	}
	if ($inputMax || $inputMax === 0) {
		$attrsOutput .= " max='" . esc_attr($inputMax) . "'";
	}
	if ($inputStep || $inputStep === 0) {
		$attrsOutput .= " step='" . esc_attr($inputStep) . "'";
	}
}

if ($inputTracking) {
	$attrsOutput .= " data-tracking='" . esc_attr($inputTracking) . "'";
}

if ($inputValue) {
	$attrsOutput .= " value='" . esc_attr($inputValue) . "'";
}

if ($inputPlaceholder) {
	$attrsOutput .= " placeholder='" . esc_attr($inputPlaceholder) . "'";
}

$input = '
<input
	class="' . esc_attr($inputClass) . '"
	name="' . esc_attr($inputName) . '"
	id="' . esc_attr($inputId) . '"
	type="' . esc_attr($inputType) . '"
	' . disabled($inputIsDisabled, true, false) . '
	' . readonly($inputIsReadOnly, true, false) . '
	' . $attrsOutput . '
/>';

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $input,
			'fieldId' => $inputId,
			'fieldName' => $inputName,
			'fieldDisabled' => !empty($inputIsDisabled),
			'fieldHideLabel' => $inputType === 'hidden',
			'fieldUseError' => $inputType !== 'hidden'
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
