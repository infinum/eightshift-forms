<?php

/**
 * Template for the Checkboxes Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Helper;
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
$additionalContent = Helper::getBlockAdditionalContentViaFilter('checkboxes', $attributes);

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
			'fieldTypeInternal' => Helper::getStateFieldType('checkboxes'),
			'fieldName' => $checkboxesName,
			'fieldIsRequired' => $checkboxesIsRequired,
			'fieldTypeCustom' => $checkboxesTypeCustom ?: 'checkbox', // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
			'fieldConditionalTags' => Components::render(
				'conditional-tags',
				Components::props('conditionalTags', $attributes)
			),
			'fieldAttrs' => $checkboxesFieldAttrs,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
