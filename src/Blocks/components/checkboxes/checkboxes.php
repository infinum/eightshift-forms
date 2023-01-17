<?php

/**
 * Template for the Checkboxes Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Hooks\Filters;

$manifest = Components::getManifest(__DIR__);

$checkboxesContent = Components::checkAttr('checkboxesContent', $attributes, $manifest);
$checkboxesName = Components::checkAttr('checkboxesName', $attributes, $manifest);
$checkboxesIsRequired = Components::checkAttr('checkboxesIsRequired', $attributes, $manifest);

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
$additionalContent = '';
$filterName = Filters::getBlockFilterName('checkboxes', 'additionalContent');
if (has_filter($filterName)) {
	$additionalContent = apply_filters($filterName, $attributes ?? []);
}

$checkboxes = '
	' . $checkboxesContent . '
	' . $additionalContent . '
';

echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $checkboxes,
			'fieldId' => $checkboxesName,
			'fieldName' => $checkboxesName,
			'fieldIsRequired' => $checkboxesIsRequired,
			'fieldConditionalTags' => Components::render(
				'conditional-tags',
				Components::props('conditionalTags', $attributes)
			),
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
