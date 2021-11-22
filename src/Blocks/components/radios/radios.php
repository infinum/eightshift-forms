<?php

/**
 * Template for the radios Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$radiosId = Components::checkAttr('radiosId', $attributes, $manifest);
$radiosContent = Components::checkAttr('radiosContent', $attributes, $manifest);
$radiosName = Components::checkAttr('radiosName', $attributes, $manifest);

// Add internal counter name key.
$radiosContent = (string) preg_replace_callback('/name=""/', function () use ($radiosName) {
	return 'name="' . $radiosName . '"';
}, $radiosContent);

// Add internal counter id key.
$indexId = 0;
$radiosContent = (string) preg_replace_callback('/id=""/', function () use (&$indexId, $radiosId) {
	return 'id="' . $radiosId . '[' . $indexId++ . ']"';
}, $radiosContent);

// Add internal counter for key.
$indexLabel = 0;
$radiosContent = (string) preg_replace_callback('/for=""/', function () use (&$indexLabel, $radiosId) {
	return 'for="' . $radiosId . '[' . $indexLabel++ . ']"';
}, $radiosContent);

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $radiosContent,
			'fieldName' => $radiosName,
			'fieldId' => $radiosId,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
