<?php

/**
 * Template for the Checkboxes Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$checkboxesName = Components::checkAttr('checkboxesName', $attributes, $manifest);
if (!$checkboxesName) {
	return;
}

$checkboxesContent = Components::checkAttr('checkboxesContent', $attributes, $manifest);
$checkboxesIsRequired = Components::checkAttr('checkboxesIsRequired', $attributes, $manifest);
$checkboxesTypeCustom = Components::checkAttr('checkboxesTypeCustom', $attributes, $manifest);
$checkboxesFieldAttrs = Components::checkAttr('checkboxesFieldAttrs', $attributes, $manifest);
$checkboxesUseLabelAsPlaceholder = Components::checkAttr('checkboxesUseLabelAsPlaceholder', $attributes, $manifest);
$checkboxesPlaceholder = Components::checkAttr('checkboxesPlaceholder', $attributes, $manifest);

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
$checkboxesFieldLabel = $attributes[Components::getAttrKey('checkboxesFieldLabel', $attributes, $manifest)] ?? '';
$checkboxesShowAs = $attributes[Components::getAttrKey('checkboxesShowAs', $attributes, $manifest)] ?? '';

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

	$placeholder = Components::render(
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
	'fieldConditionalTags' => Components::render('conditional-tags', Components::props('conditionalTags', $attributes)),
	'fieldAttrs' => $checkboxesFieldAttrs,
];

// Hide label if needed but separated like this so we can utilize normal fieldHideLabel attribute from field component.
if ($checkboxesHideLabel) {
	$fieldOutput['fieldHideLabel'] = true;
}

echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes, $fieldOutput),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
