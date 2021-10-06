<?php

/**
 * Template for the file Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$fileId = Components::checkAttr('fileId', $attributes, $manifest);
$fileName = Components::checkAttr('fileName', $attributes, $manifest);
$fileAccept = Components::checkAttr('fileAccept', $attributes, $manifest);
$fileIsMultiple = Components::checkAttr('fileIsMultiple', $attributes, $manifest);
$fileIsRequired = Components::checkAttr('fileIsRequired', $attributes, $manifest);
$fileTracking = Components::checkAttr('fileTracking', $attributes, $manifest);
$fileMinSize = Components::checkAttr('fileMinSize', $attributes, $manifest);
$fileMaxSize = Components::checkAttr('fileMaxSize', $attributes, $manifest);

// Fix for getting attribute that is part of the child component.
$fileFieldLabel = $attributes[Components::getAttrKey('fileFieldLabel', $attributes, $manifest)] ?? '';

$fileClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
]);

$fileIsMultiple = $fileIsMultiple ? 'multiple' : '';

$file = '
	<input
		class="' . esc_attr($fileClass) . '"
		name="' . esc_attr($fileName) . '"
		id="' . esc_attr($fileId) . '"
		type="file"
		data-validation-accept="' . $fileAccept . '"
		data-validation-required="' . $fileIsRequired . '"
		data-validation-min-size="' . $fileMinSize . '"
		data-validation-max-size="' . $fileMaxSize . '"
		data-tracking="' . $fileTracking . '"
		' . $fileIsMultiple . '
	/>
';

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $file,
			'fieldId' => $fileId,
			'fieldName' => $fileName,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
		]
	)
);
