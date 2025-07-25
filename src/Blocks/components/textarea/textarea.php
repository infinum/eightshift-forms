<?php

/**
 * Template for the Textarea Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$textareaName = Helpers::checkAttr('textareaName', $attributes, $manifest);
if (!$textareaName) {
	return;
}
$textareaValue = Helpers::checkAttr('textareaValue', $attributes, $manifest);
$textareaPlaceholder = Helpers::checkAttr('textareaPlaceholder', $attributes, $manifest);
$textareaIsDisabled = Helpers::checkAttr('textareaIsDisabled', $attributes, $manifest);
$textareaIsReadOnly = Helpers::checkAttr('textareaIsReadOnly', $attributes, $manifest);
$textareaIsRequired = Helpers::checkAttr('textareaIsRequired', $attributes, $manifest);
$textareaTracking = Helpers::checkAttr('textareaTracking', $attributes, $manifest);
$textareaAttrs = Helpers::checkAttr('textareaAttrs', $attributes, $manifest);
$textareaIsMonospace = Helpers::checkAttr('textareaIsMonospace', $attributes, $manifest);
$textareaSaveAsJson = Helpers::checkAttr('textareaSaveAsJson', $attributes, $manifest);
$textareaTypeCustom = Helpers::checkAttr('textareaTypeCustom', $attributes, $manifest);
$textareaFieldAttrs = Helpers::checkAttr('textareaFieldAttrs', $attributes, $manifest);
$textareaSize = Helpers::checkAttr('textareaSize', $attributes, $manifest);
$textareaLimitHeight = Helpers::checkAttr('textareaLimitHeight', $attributes, $manifest);
$textareaIsPreventSubmit = Helpers::checkAttr('textareaIsPreventSubmit', $attributes, $manifest);
$textareaUseLabelAsPlaceholder = Helpers::checkAttr('textareaUseLabelAsPlaceholder', $attributes, $manifest);
$textareaTwSelectorsData = Helpers::checkAttr('textareaTwSelectorsData', $attributes, $manifest);

$textareaId = $textareaName . '-' . Helpers::getUnique();

// Fix for getting attribute that is part of the child component.
$textareaHideLabel = false;
$textareaFieldLabel = $attributes[Helpers::getAttrKey('textareaFieldLabel', $attributes, $manifest)] ?? '';

$twClasses = FormsHelper::getTwSelectors($textareaTwSelectorsData, ['textarea']);

$textareaClass = Helpers::classnames([
	FormsHelper::getTwBase($twClasses, 'textarea', $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
	Helpers::selector($textareaIsMonospace, $componentClass, '', 'monospace'),
	Helpers::selector($textareaSize, $componentClass, 'size', $textareaSize),
	Helpers::selector($textareaLimitHeight, $componentClass, '', 'limit-height'),
]);

if ($textareaSaveAsJson) {
	$textareaAttrs[UtilsHelper::getStateAttribute('saveAsJson')] = esc_attr($textareaSaveAsJson);
}

// Set to use in settings for preventing field submit.
if ($textareaIsPreventSubmit) {
	$textareaFieldAttrs[UtilsHelper::getStateAttribute('fieldPreventSubmit')] = esc_attr($textareaIsPreventSubmit);
}

if ($textareaPlaceholder) {
	$textareaAttrs['placeholder'] = esc_attr($textareaPlaceholder);
}

if ($textareaUseLabelAsPlaceholder) {
	$textareaAttrs['placeholder'] = esc_attr($textareaFieldLabel);
	$textareaHideLabel = true;
}

if ($textareaIsRequired) {
	$textareaAttrs['aria-required'] = 'true';
}

$textareaAttrs['aria-invalid'] = 'false';

// Additional content filter.
$additionalContent = UtilsGeneralHelper::getBlockAdditionalContentViaFilter('textarea', $attributes);

$textarea = '<textarea
		class="' . esc_attr($textareaClass) . '"
		name="' . esc_attr($textareaName) . '"
		id="' . esc_attr($textareaId) . '"
		' . disabled($textareaIsDisabled, true, false) . '
		' . wp_readonly($textareaIsReadOnly, true, false) . '
		' . Helpers::getAttrsOutput($textareaAttrs) . '
	>' . wp_kses_post($textareaValue) . '</textarea>
	' . $additionalContent . '
';

echo Helpers::render(
	'field',
	array_merge(
		Helpers::props('field', $attributes, [
			'fieldContent' => $textarea,
			'fieldId' => $textareaId,
			'fieldName' => $textareaName,
			'fieldTwSelectorsData' => $textareaTwSelectorsData,
			'fieldTypeInternal' => FormsHelper::getStateFieldType('textarea'),
			'fieldIsRequired' => $textareaIsRequired,
			'fieldDisabled' => !empty($textareaIsDisabled),
			'fieldTypeCustom' => $textareaTypeCustom ?: 'textarea', // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
			'fieldTracking' => $textareaTracking,
			'fieldHideLabel' => $textareaHideLabel,
			'fieldConditionalTags' => Helpers::render(
				'conditional-tags',
				Helpers::props('conditionalTags', $attributes)
			),
			'fieldAttrs' => $textareaFieldAttrs,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
