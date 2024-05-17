<?php

/**
 * Template for the Input Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

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

// Fix for getting attribute that is part of the child component.
$inputHideLabel = false;
$inputFieldLabel = $attributes[Helpers::getAttrKey('inputFieldLabel', $attributes, $manifest)] ?? '';

$inputClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
	Helpers::selector($inputSingleSubmit && $inputType === 'range', UtilsHelper::getStateSelectorAdmin('singleSubmit')),
	Helpers::selector($inputType === 'range', $componentClass, 'range'),
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
		$cssSelector = Helpers::classnames([
			UtilsHelper::getStateSelector('inputRangeMin'),
			Helpers::selector($componentClass, $componentClass, 'range', 'min'),
		]);

		$additionalContent .= wp_kses_post("<span class='{$cssSelector}'>{$inputRangeShowMinPrefix}{$inputAttrs['min']}{$inputRangeShowMinSuffix}</span>");
	}

	if ($inputRangeShowCurrent) {
		$cssSelector = Helpers::selector($componentClass, $componentClass, 'range', 'current');
		$cssJsSelector = UtilsHelper::getStateSelector('inputRangeCurrent');

		$additionalContent .= wp_kses_post("<span class='{$cssSelector}'>{$inputRangeShowCurrentPrefix}<span class='{$cssJsSelector}'>{$inputAttrs['value']}</span>{$inputRangeShowCurrentSuffix}</span>");
	}

	if ($inputRangeShowMax) {
		$cssSelector = Helpers::classnames([
			UtilsHelper::getStateSelector('inputRangeMax'),
			Helpers::selector($componentClass, $componentClass, 'range', 'max'),
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

echo Helpers::render(
	'field',
	array_merge(
		Helpers::props('field', $attributes, [
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
			'fieldConditionalTags' => Helpers::render(
				'conditional-tags',
				Helpers::props('conditionalTags', $attributes)
			),
			'fieldAttrs' => $inputFieldAttrs,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? ''
		]
	)
);
