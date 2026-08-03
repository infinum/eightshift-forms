<?php

/**
 * Template for the Checkboxes Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$checkboxesName = Helpers::checkAttr('checkboxesName', $attributes, $manifest);
if (!$checkboxesName) {
	return;
}

$checkboxesContent = Helpers::checkAttr('checkboxesContent', $attributes, $manifest);
$checkboxesIsRequired = Helpers::checkAttr('checkboxesIsRequired', $attributes, $manifest);
$checkboxesTypeCustom = Helpers::checkAttr('checkboxesTypeCustom', $attributes, $manifest);
$checkboxesFieldAttrs = Helpers::checkAttr('checkboxesFieldAttrs', $attributes, $manifest);
$checkboxesUseLabelAsPlaceholder = Helpers::checkAttr('checkboxesUseLabelAsPlaceholder', $attributes, $manifest);
$checkboxesPlaceholder = Helpers::checkAttr('checkboxesPlaceholder', $attributes, $manifest);
$checkboxesShowAs = $attributes[Helpers::getAttrKey('checkboxesShowAs', $attributes, $manifest)] ?? '';

$twSelectorsData = FormsHelper::getTwSelectorsData($attributes);
$checkboxesFieldStyle = is_admin() ? [] : Helpers::checkAttr(Helpers::getAttrKey('fieldStyle', $attributes, $manifest), $attributes, $manifest);

$fieldStyleOverrides = [
	'content' => FormsHelper::getTwFieldStyleOutput($twSelectorsData, $checkboxesFieldStyle, $checkboxesShowAs ?: $manifest['componentName'], 'content'),
	'label' => FormsHelper::getTwFieldStyleOutput($twSelectorsData, $checkboxesFieldStyle, $checkboxesShowAs ?: $manifest['componentName'], 'label'),
	'label-icon' => FormsHelper::getTwFieldStyleOutput($twSelectorsData, $checkboxesFieldStyle, $checkboxesShowAs ?: $manifest['componentName'], 'label-icon'),
	'label-inner' => FormsHelper::getTwFieldStyleOutput($twSelectorsData, $checkboxesFieldStyle, $checkboxesShowAs ?: $manifest['componentName'], 'label-inner'),
	'help' => FormsHelper::getTwFieldStyleOutput($twSelectorsData, $checkboxesFieldStyle, $checkboxesShowAs ?: $manifest['componentName'], 'help'),
	'input' => FormsHelper::getTwFieldStyleOutput($twSelectorsData, $checkboxesFieldStyle, $checkboxesShowAs ?: $manifest['componentName'], 'input'),
];

// Replace field style overrides.
$checkboxesContent = str_replace('%FSO_CONTENT%', $fieldStyleOverrides['content'], $checkboxesContent);
$checkboxesContent = str_replace('%FSO_LABEL%', $fieldStyleOverrides['label'], $checkboxesContent);
$checkboxesContent = str_replace('%FSO_LABEL_ICON%', $fieldStyleOverrides['label-icon'], $checkboxesContent);
$checkboxesContent = str_replace('%FSO_LABEL_INNER%', $fieldStyleOverrides['label-inner'], $checkboxesContent);
$checkboxesContent = str_replace('%FSO_HELP%', $fieldStyleOverrides['help'], $checkboxesContent);
$checkboxesContent = str_replace('%FSO_INPUT%', $fieldStyleOverrides['input'], $checkboxesContent);

$checkboxesId = $checkboxesName . '-' . Helpers::getUnique();

// Add internal counter name key.
$checkboxesContent = (string) preg_replace_callback('/name=""/', fn(): string => 'name="' . $checkboxesName . '"', (string) $checkboxesContent);

// Add internal counter id key.
$indexId = 0;
$checkboxesContent = (string) preg_replace_callback('/id=""/', function () use (&$indexId, $checkboxesId): string {
	return 'id="' . $checkboxesId . '[' . $indexId++ . ']"';
}, $checkboxesContent);

// Add internal counter for key.
$indexLabel = 0;
$checkboxesContent = (string) preg_replace_callback('/for=""/', function () use (&$indexLabel, $checkboxesId): string {
	return 'for="' . $checkboxesId . '[' . $indexLabel++ . ']"';
}, $checkboxesContent);

// Additional content filter.
$additionalContent = GeneralHelpers::getBlockAdditionalContentViaFilter('checkboxes', $attributes);

$placeholderLabel = '';
$placeholder = '';
$checkboxesHideLabel = false;
$checkboxesFieldLabel = $attributes[Helpers::getAttrKey('checkboxesFieldLabel', $attributes, $manifest)] ?? '';

// Checkboxes don't use placeholder so we are not going to render it.
if ($checkboxesShowAs !== '') {
	// Placeholder input value.
	if ($checkboxesPlaceholder) {
		$placeholderLabel = $checkboxesPlaceholder;
	}

	// Placeholder label for value.
	if ($checkboxesUseLabelAsPlaceholder) {
		$checkboxesHideLabel = true;
		$placeholderLabel = esc_attr($checkboxesFieldLabel) ?: esc_html__('Select option', 'eightshift-forms'); // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
	}

	$placeholder = Helpers::render(
		'checkbox',
		[
			'checkboxLabel' => $placeholderLabel,
			'checkboxAsPlaceholder' => true,
			'checkboxIsHidden' => true,
		]
	);
}

$checkboxes = "
	{$placeholder}
	{$checkboxesContent}
	{$additionalContent}
";

$fieldOutput = [
	'fieldContent' => $checkboxes,
	'fieldId' => $checkboxesId,
	'fieldTypeInternal' => FormsHelper::getStateFieldType('checkboxes'),
	'fieldName' => $checkboxesName,
	'fieldTwSelectorsData' => $twSelectorsData,
	'fieldIsRequired' => $checkboxesIsRequired,
	'fieldTypeCustom' => $checkboxesTypeCustom ?: 'checkbox', // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
	'fieldConditionalTags' => Helpers::render('conditional-tags', Helpers::props('conditionalTags', $attributes)),
	'fieldAttrs' => array_merge($checkboxesFieldAttrs, [
		'role' => 'group',
		'aria-labelledby' => $checkboxesId,
	]),
];

// Hide label if needed but separated like this so we can utilize normal fieldHideLabel attribute from field component.
if ($checkboxesHideLabel) {
	$fieldOutput['fieldHideLabel'] = true;
}

echo Helpers::render(
	'field',
	array_merge(
		Helpers::props('field', $attributes, $fieldOutput),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
