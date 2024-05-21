<?php

/**
 * Template for the radios Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$radiosName = Helpers::checkAttr('radiosName', $attributes, $manifest);
if (!$radiosName) {
	return;
}

$radiosContent = Helpers::checkAttr('radiosContent', $attributes, $manifest);
$radiosIsRequired = Helpers::checkAttr('radiosIsRequired', $attributes, $manifest);
$radiosTypeCustom = Helpers::checkAttr('radiosTypeCustom', $attributes, $manifest);
$radiosFieldAttrs = Helpers::checkAttr('radiosFieldAttrs', $attributes, $manifest);
$radiosTracking = Helpers::checkAttr('radiosTracking', $attributes, $manifest);
$radiosUseLabelAsPlaceholder = Helpers::checkAttr('radiosUseLabelAsPlaceholder', $attributes, $manifest);
$radiosPlaceholder = Helpers::checkAttr('radiosPlaceholder', $attributes, $manifest);

// Add internal counter name key.
$radiosContent = (string) preg_replace_callback('/name=""/', function () use ($radiosName) {
	return 'name="' . $radiosName . '"';
}, $radiosContent);

// Add internal counter id key.
$indexId = 0;
$radiosContent = (string) preg_replace_callback('/id=""/', function () use (&$indexId, $radiosName) {
	return 'id="' . $radiosName . '[' . $indexId++ . ']"';
}, $radiosContent);

// Add internal counter for key.
$indexLabel = 0;
$radiosContent = (string) preg_replace_callback('/for=""/', function () use (&$indexLabel, $radiosName) {
	return 'for="' . $radiosName . '[' . $indexLabel++ . ']"';
}, $radiosContent);

// Additional content filter.
$additionalContent = UtilsGeneralHelper::getBlockAdditionalContentViaFilter('radios', $attributes);


$placeholderLabel = '';
$placeholder = '';
$radiosHideLabel = false;
$radiosFieldLabel = $attributes[Helpers::getAttrKey('radiosFieldLabel', $attributes, $manifest)] ?? '';
$radiosShowAs = $attributes[Helpers::getAttrKey('radiosShowAs', $attributes, $manifest)] ?? '';

// Radios don't use placeholder so we are not going to render it.
if ($radiosShowAs !== '') {
	// Placeholder input value.
	if ($radiosPlaceholder) {
		$placeholderLabel = $radiosPlaceholder;
	}

	// Placeholder label for value.
	if ($radiosUseLabelAsPlaceholder) {
		$radiosHideLabel = true;
		$placeholderLabel = esc_attr($radiosFieldLabel) ?: esc_html__('Select option', 'eightshift-forms'); // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
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

$radios = "
	{$placeholder}
	{$radiosContent}
	{$additionalContent}
";

$fieldOutput = [
	'fieldContent' => $radios,
	'fieldName' => $radiosName,
	'fieldIsRequired' => $radiosIsRequired,
	'fieldTypeInternal' => FormsHelper::getStateFieldType('radios'),
	'fieldId' => $radiosName,
	'fieldTracking' => $radiosTracking,
	'fieldTypeCustom' => $radiosTypeCustom ?: 'radio', // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
	'fieldConditionalTags' => Helpers::render('conditional-tags', Helpers::props('conditionalTags', $attributes)),
	'fieldAttrs' => $radiosFieldAttrs,
];

// Hide label if needed but separated like this so we can utilize normal fieldHideLabel attribute from field component.
if ($radiosHideLabel) {
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
