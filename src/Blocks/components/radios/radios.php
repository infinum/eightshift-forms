<?php

/**
 * Template for the radios Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Helper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);
$manifestTypeInternal = Components::getSettings()['typeInternal'];

$radiosName = Components::checkAttr('radiosName', $attributes, $manifest);
if (!$radiosName) {
	return;
}

$radiosContent = Components::checkAttr('radiosContent', $attributes, $manifest);
$radiosIsRequired = Components::checkAttr('radiosIsRequired', $attributes, $manifest);
$radiosTypeCustom = Components::checkAttr('radiosTypeCustom', $attributes, $manifest);
$radiosFieldAttrs = Components::checkAttr('radiosFieldAttrs', $attributes, $manifest);
$radiosTracking = Components::checkAttr('radiosTracking', $attributes, $manifest);

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
$additionalContent = Helper::getBlockAdditionalContentViaFilter('radios', $attributes);

$radios = '
	' . $radiosContent . '
	' . $additionalContent . '
';

echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $radios,
			'fieldName' => $radiosName,
			'fieldIsRequired' => $radiosIsRequired,
			'fieldTypeInternal' => $manifestTypeInternal['radios'],
			'fieldId' => $radiosName,
			'fieldTracking' => $radiosTracking,
			'fieldTypeCustom' => $radiosTypeCustom ?: 'radio', // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
			'fieldConditionalTags' => Components::render(
				'conditional-tags',
				Components::props('conditionalTags', $attributes)
			),
			'fieldAttrs' => $radiosFieldAttrs,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
