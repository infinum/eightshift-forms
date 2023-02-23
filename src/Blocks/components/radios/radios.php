<?php

/**
 * Template for the radios Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Helper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$radiosContent = Components::checkAttr('radiosContent', $attributes, $manifest);
$radiosName = Components::checkAttr('radiosName', $attributes, $manifest);
$radiosIsRequired = Components::checkAttr('radiosIsRequired', $attributes, $manifest);

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
			'fieldId' => $radiosName,
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
