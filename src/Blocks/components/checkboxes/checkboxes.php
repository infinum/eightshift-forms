<?php

/**
 * Template for the Checkboxes Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

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

// Add internal counter name key.
$checkboxesContent = (string) preg_replace_callback('/name=""/', function () use ($checkboxesName) {
	return 'name="' . $checkboxesName . '"';
}, $checkboxesContent);

// Add internal counter id key.
$indexId = 0;
$checkboxesContent = (string) preg_replace_callback('/id=""/', function () use (&$indexId, $checkboxesName) {
	return 'id="' . $checkboxesName . '[' . $indexId++ . ']"';
}, $checkboxesContent);

// Add internal counter for key.
$indexLabel = 0;
$checkboxesContent = (string) preg_replace_callback('/for=""/', function () use (&$indexLabel, $checkboxesName) {
	return 'for="' . $checkboxesName . '[' . $indexLabel++ . ']"';
}, $checkboxesContent);

// Additional content filter.
$additionalContent = UtilsGeneralHelper::getBlockAdditionalContentViaFilter('checkboxes', $attributes);

$placeholderLabel = '';
$placeholder = '';
$checkboxesHideLabel = false;
$checkboxesFieldLabel = $attributes[Helpers::getAttrKey('checkboxesFieldLabel', $attributes, $manifest)] ?? '';
$checkboxesShowAs = $attributes[Helpers::getAttrKey('checkboxesShowAs', $attributes, $manifest)] ?? '';

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
	'fieldId' => $checkboxesName,
	'fieldTypeInternal' => FormsHelper::getStateFieldType('checkboxes'),
	'fieldName' => $checkboxesName,
	'fieldIsRequired' => $checkboxesIsRequired,
	'fieldTypeCustom' => $checkboxesTypeCustom ?: 'checkbox', // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
	'fieldConditionalTags' => Helpers::render('conditional-tags', Helpers::props('conditionalTags', $attributes)),
	'fieldAttrs' => $checkboxesFieldAttrs,
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
