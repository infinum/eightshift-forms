<?php

/**
 * Template for the Input Component.
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
$inputSingleSubmit = Components::checkAttr('inputSingleSubmit', $attributes, $manifest);
$inputRangeShowMin = Components::checkAttr('inputRangeShowMin', $attributes, $manifest);
$inputRangeShowMinPrefix = Components::checkAttr('inputRangeShowMinPrefix', $attributes, $manifest);
$inputRangeShowMinSuffix = Components::checkAttr('inputRangeShowMinSuffix', $attributes, $manifest);
$inputRangeShowMax = Components::checkAttr('inputRangeShowMax', $attributes, $manifest);
$inputRangeShowMaxPrefix = Components::checkAttr('inputRangeShowMaxPrefix', $attributes, $manifest);
$inputRangeShowMaxSuffix = Components::checkAttr('inputRangeShowMaxSuffix', $attributes, $manifest);
$inputRangeShowCurrent = Components::checkAttr('inputRangeShowCurrent', $attributes, $manifest);
$inputRangeShowCurrentPrefix = Components::checkAttr('inputRangeShowCurrentPrefix', $attributes, $manifest);
$inputRangeShowCurrentSuffix = Components::checkAttr('inputRangeShowCurrentSuffix', $attributes, $manifest);

// Fix for getting attribute that is part of the child component.
$inputHideLabel = false;
$inputFieldLabel = $attributes[Components::getAttrKey('inputFieldLabel', $attributes, $manifest)] ?? '';

$inputClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($inputSingleSubmit && $inputType === 'range', UtilsHelper::getStateSelectorAdmin('singleSubmit')),
	Components::selector($inputType === 'range', $componentClass, 'range'),
]);

// Additional content filter.
$additionalContent = UtilsGeneralHelper::getBlockAdditionalContentViaFilter('input', $attributes);

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

if ($inputType === 'range') {
	$inputAttrs['min'] = esc_attr($inputMin);
	$inputAttrs['max'] = esc_attr($inputMax);
	$inputAttrs['step'] = esc_attr($inputStep);

	if (!$inputValue) {
		$inputAttrs['value'] = esc_attr($inputMin);
	}

	if ($inputRangeShowMin) {
		$cssSelector = Components::classnames([
			UtilsHelper::getStateSelector('inputRangeMin'),
			Components::selector($componentClass, $componentClass, 'range', 'min'),
		]);

		$additionalContent .= wp_kses_post("<span class='{$cssSelector}'>{$inputRangeShowMinPrefix}{$inputAttrs['min']}{$inputRangeShowMinSuffix}</span>");
	}

	if ($inputRangeShowCurrent) {
		$cssSelector = Components::selector($componentClass, $componentClass, 'range', 'current');
		$cssJsSelector = UtilsHelper::getStateSelector('inputRangeCurrent');

		$additionalContent .= wp_kses_post("<span class='{$cssSelector}'>{$inputRangeShowCurrentPrefix}<span class='{$cssJsSelector}'>{$inputAttrs['value']}</span>{$inputRangeShowCurrentSuffix}</span>");
	}

	if ($inputRangeShowMax) {
		$cssSelector = Components::classnames([
			UtilsHelper::getStateSelector('inputRangeMax'),
			Components::selector($componentClass, $componentClass, 'range', 'max'),
		]);

		$additionalContent .= wp_kses_post("<span class='{$cssSelector}'>{$inputRangeShowMaxPrefix}{$inputAttrs['max']}{$inputRangeShowMaxSuffix}</span>");
	}
}

$inputAttrsOutput = '';
if ($inputAttrs) {
	foreach ($inputAttrs as $key => $value) {
		$inputAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

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
