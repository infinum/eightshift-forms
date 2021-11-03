<?php

/**
 * Template for the Checkboxes Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$checkboxesId = Components::checkAttr('checkboxesId', $attributes, $manifest);
$checkboxesContent = Components::checkAttr('checkboxesContent', $attributes, $manifest);
$checkboxesName = Components::checkAttr('checkboxesName', $attributes, $manifest);

// Add internal counter name key.
$checkboxesContent = (string) preg_replace_callback('/name=""/', function () use ($checkboxesName) {
	return 'name="' . $checkboxesName . '"';
}, $checkboxesContent);

// Add internal counter id key.
$indexId = 0;
$checkboxesContent = (string) preg_replace_callback('/id=""/', function () use (&$indexId, $checkboxesId) {
	return 'id="' . $checkboxesId . '[' . $indexId++ . ']"';
}, $checkboxesContent);

// Add internal counter for key.
$indexLabel = 0;
$checkboxesContent = (string) preg_replace_callback('/for=""/', function () use (&$indexLabel, $checkboxesId) {
	return 'for="' . $checkboxesId . '[' . $indexLabel++ . ']"';
}, $checkboxesContent);

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $checkboxesContent,
			'fieldId' => $checkboxesId,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
		]
	)
);
