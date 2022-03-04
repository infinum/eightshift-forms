<?php

/**
 * Template for the Input Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;
use EightshiftForms\Hooks\Filters;

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
$inputIsRequired = Components::checkAttr('inputIsRequired', $attributes, $manifest);
$inputTracking = Components::checkAttr('inputTracking', $attributes, $manifest);
$inputMin = Components::checkAttr('inputMin', $attributes, $manifest);
$inputMax = Components::checkAttr('inputMax', $attributes, $manifest);
$inputStep = Components::checkAttr('inputStep', $attributes, $manifest);
$inputAttrs = Components::checkAttr('inputAttrs', $attributes, $manifest);

// Fix for getting attribute that is part of the child component.
$inputFieldLabel = $attributes[Components::getAttrKey('inputFieldLabel', $attributes, $manifest)] ?? '';

$inputClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
]);

if ($inputType === 'number') {
	if ($inputMin || $inputMin === 0) {
		$inputAttrs['min'] = esc_attr($inputMin);
	}
	if ($inputMax || $inputMax === 0) {
		$inputAttrs['max'] = esc_attr($inputMax);
	}
	if ($inputStep || $inputStep === 0) {
		$inputAttrs['step'] = esc_attr($inputStep);
	}
}

if ($inputTracking) {
	$inputAttrs['data-tracking'] = esc_attr($inputTracking);
}

if ($inputValue) {
	$inputAttrs['value'] = esc_attr($inputValue);
}

if ($inputPlaceholder) {
	$inputAttrs['placeholder'] = esc_attr($inputPlaceholder);
}

$inputAttrsOutput = '';
if ($inputAttrs) {
	foreach ($inputAttrs as $key => $value) {
		$inputAttrsOutput .= \wp_kses_post(" {$key}='" . $value . "'");
	}
}

// Additional content filter.
$additionalContent = '';
$filterName = Filters::getBlockFilterName('input', 'additionalContent');
if (has_filter($filterName)) {
	$additionalContent = apply_filters($filterName, $attributes ?? []);
}

$isWpFiveNine = \is_wp_version_compatible('5.9');
$input = '
	<input
		class="' . esc_attr($inputClass) . '"
		name="' . esc_attr($inputName) . '"
		id="' . esc_attr($inputId) . '"
		type="' . esc_attr($inputType) . '"
		' . disabled($inputIsDisabled, true, false) . '
		' . ($isWpFiveNine ? wp_readonly($inputIsReadOnly, true, false) : readonly($inputIsReadOnly, true, false)) . /* @phpstan-ignore-line */ '
		' . $inputAttrsOutput . '
	/>
	' . $additionalContent . '
';

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $input,
			'fieldId' => $inputId,
			'fieldName' => $inputName,
			'fieldIsRequired' => $inputIsRequired,
			'fieldDisabled' => !empty($inputIsDisabled),
			'fieldHideLabel' => $inputType === 'hidden',
			'fieldUseError' => $inputType !== 'hidden',
			'fieldAttrs' => [
				'data-input-type' => $inputType,
			],
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? ''
		]
	)
);
