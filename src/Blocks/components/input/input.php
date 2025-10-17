<?php

/**
 * Template for the Input Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$inputName = Helpers::checkAttr('inputName', $attributes, $manifest);
if (!$inputName) {
	return;
}

$inputValue = Helpers::checkAttr('inputValue', $attributes, $manifest);
$inputPlaceholder = Helpers::checkAttr('inputPlaceholder', $attributes, $manifest);
$inputType = Helpers::checkAttr('inputType', $attributes, $manifest);
$inputTypeCustom = Helpers::checkAttr('inputTypeCustom', $attributes, $manifest);
$inputIsDisabled = Helpers::checkAttr('inputIsDisabled', $attributes, $manifest);
$inputIsReadOnly = Helpers::checkAttr('inputIsReadOnly', $attributes, $manifest);
$inputIsRequired = Helpers::checkAttr('inputIsRequired', $attributes, $manifest);
$inputTracking = Helpers::checkAttr('inputTracking', $attributes, $manifest);
$inputMin = Helpers::checkAttr('inputMin', $attributes, $manifest);
$inputMax = Helpers::checkAttr('inputMax', $attributes, $manifest);
$inputStep = Helpers::checkAttr('inputStep', $attributes, $manifest);
$inputAttrs = Helpers::checkAttr('inputAttrs', $attributes, $manifest);
$inputFieldAttrs = Helpers::checkAttr('inputFieldAttrs', $attributes, $manifest);
$inputUseLabelAsPlaceholder = Helpers::checkAttr('inputUseLabelAsPlaceholder', $attributes, $manifest);
$inputSingleSubmit = Helpers::checkAttr('inputSingleSubmit', $attributes, $manifest);
$inputRangeShowMin = Helpers::checkAttr('inputRangeShowMin', $attributes, $manifest);
$inputRangeShowMinPrefix = Helpers::checkAttr('inputRangeShowMinPrefix', $attributes, $manifest);
$inputRangeShowMinSuffix = Helpers::checkAttr('inputRangeShowMinSuffix', $attributes, $manifest);
$inputRangeShowMax = Helpers::checkAttr('inputRangeShowMax', $attributes, $manifest);
$inputRangeShowMaxPrefix = Helpers::checkAttr('inputRangeShowMaxPrefix', $attributes, $manifest);
$inputRangeShowMaxSuffix = Helpers::checkAttr('inputRangeShowMaxSuffix', $attributes, $manifest);
$inputRangeShowCurrent = Helpers::checkAttr('inputRangeShowCurrent', $attributes, $manifest);
$inputRangeShowCurrentPrefix = Helpers::checkAttr('inputRangeShowCurrentPrefix', $attributes, $manifest);
$inputRangeShowCurrentSuffix = Helpers::checkAttr('inputRangeShowCurrentSuffix', $attributes, $manifest);
$inputRangeUseCustomField = Helpers::checkAttr('inputRangeUseCustomField', $attributes, $manifest);
$inputTwSelectorsData = Helpers::checkAttr('inputTwSelectorsData', $attributes, $manifest);

$inputId = $inputName . '-' . Helpers::getUnique();

// Fix for getting attribute that is part of the child component.
$inputHideLabel = false;
$inputFieldLabel = $attributes[Helpers::getAttrKey('inputFieldLabel', $attributes, $manifest)] ?? '';

$twClasses = FormsHelper::getTwSelectors($inputTwSelectorsData, ['input', 'range']);

$inputClass = Helpers::classnames([
	$inputType === 'range' ? FormsHelper::getTwBase($twClasses, 'range', "{$componentClass}__range") : FormsHelper::getTwBase($twClasses, 'input', $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
	Helpers::selector($inputSingleSubmit && $inputType === 'range', UtilsHelper::getStateSelectorAdmin('singleSubmit')),
]);

// Additional content filter.
$additionalContent = GeneralHelpers::getBlockAdditionalContentViaFilter('input', $attributes);

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
	// Fallback is the browser default value if no min is set.
	// Without fallback .value in JS returns a mid value between min (or 0 if unset) and max (or 100 if unset), which can cause weird display issue.
	$inputAttrs['min'] = esc_attr($inputMin ?: 0);

	if ($inputMax) {
		$inputAttrs['max'] = esc_attr($inputMax);
	}

	if ($inputStep) {
		$inputAttrs['step'] = esc_attr($inputStep);
	}

	if (!$inputValue) {
		$inputAttrs['value'] = esc_attr($inputAttrs['min']);
	}

	if ($inputRangeShowMin) {
		$cssSelector = Helpers::classnames([
			UtilsHelper::getStateSelector('inputRangeMin'),
			FormsHelper::getTwPart($twClasses, 'range', 'min', "{$componentClass}__range--min"),
		]);

		$min = $inputAttrs['min'] ?? '';

		$additionalContent .= wp_kses_post("<span class='{$cssSelector}'>{$inputRangeShowMinPrefix}{$min}{$inputRangeShowMinSuffix}</span>");
	}

	if ($inputRangeShowCurrent) {
		$cssSelector = FormsHelper::getTwPart($twClasses, 'range', 'current', "{$componentClass}__range--current");
		$cssJsSelector = UtilsHelper::getStateSelector('inputRangeCurrent');

		$additionalContent .= wp_kses_post("<span class='{$cssSelector}'>{$inputRangeShowCurrentPrefix}<span class='{$cssJsSelector}'>{$inputAttrs['value']}</span>{$inputRangeShowCurrentSuffix}</span>");
	}

	if ($inputRangeShowMax) {
		$cssSelector = Helpers::classnames([
			UtilsHelper::getStateSelector('inputRangeMax'),
			FormsHelper::getTwPart($twClasses, 'range', 'max', "{$componentClass}__range--max"),
		]);

		$max = $inputAttrs['max'] ?? '';

		$additionalContent .= wp_kses_post("<span class='{$cssSelector}'>{$inputRangeShowMaxPrefix}{$max}{$inputRangeShowMaxSuffix}</span>");
	}
}

if ($inputType === 'hidden') {
	$inputAttrs['autocomplete'] = 'off';
}

if ($inputType === 'email') {
	$inputAttrs['autocomplete'] = 'email';
}

if ($inputIsRequired) {
	$inputAttrs['aria-required'] = 'true';
}

$inputAttrs['aria-invalid'] = 'false';

$input = '
	<input
		class="' . esc_attr($inputClass) . '"
		name="' . esc_attr($inputName) . '"
		id="' . esc_attr($inputId) . '"
		type="' . esc_attr($inputType) . '"
		' . disabled($inputIsDisabled, true, false) . '
		' . wp_readonly($inputIsReadOnly, true, false) . '
		' . Helpers::getAttrsOutput($inputAttrs) . '
	/>
';

if ($inputRangeUseCustomField && $inputType === 'range') {
	$input .= '<input
		class="' . esc_attr(FormsHelper::getTwBase($twClasses, 'range', "{$componentClass}__range-custom")) . '"
		type="number"
		' . disabled($inputIsDisabled, true, false) . '
		' . wp_readonly($inputIsReadOnly, true, false) . '
		' . Helpers::getAttrsOutput($inputAttrs) . '
	/>';
}

if ($additionalContent) {
	$input .= $additionalContent;
}

echo Helpers::render(
	'field',
	array_merge(
		Helpers::props('field', $attributes, [
			'fieldContent' => $input,
			'fieldId' => $inputId,
			'fieldName' => $inputName,
			'fieldTwSelectorsData' => $inputTwSelectorsData,
			'fieldTypeInternal' => FormsHelper::getStateFieldType($inputType === 'range' ? 'range' : 'input'),
			'fieldIsRequired' => $inputIsRequired,
			'fieldDisabled' => !empty($inputIsDisabled),
			'fieldUseError' => $inputType !== 'hidden',
			'fieldTypeCustom' => $inputTypeCustom ?: $inputType, // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
			'fieldTracking' => $inputTracking,
			'fieldHideLabel' => $inputHideLabel || $inputType === 'hidden',
			'fieldConditionalTags' => Helpers::render(
				'conditional-tags',
				Helpers::props('conditionalTags', $attributes)
			),
			'fieldAttrs' => $inputFieldAttrs,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $inputType === 'range' ? 'range' : $manifest['componentName'] ?? '',
		]
	)
);
