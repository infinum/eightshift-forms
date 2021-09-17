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

$fileName = Components::checkAttr('fileName', $attributes, $manifest);
$fileAccept = Components::checkAttr('fileAccept', $attributes, $manifest);
$fileId = Components::checkAttr('fileId', $attributes, $manifest);
$fileIsMultiple = Components::checkAttr('fileIsMultiple', $attributes, $manifest);

$fileClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
]);

$fileIsMultiple = $fileIsMultiple ? 'multiple' : '';

$file = '
	<input
		class="'. esc_attr($fileClass) .'"
		name="'. esc_attr($fileName) . '"
		id="'. esc_attr($fileId) . '"
		accept="'. esc_attr($fileAccept) . '"
		type="file"
		' . $fileIsMultiple . '
	/>
';

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'field',
	Components::props('field', $attributes, [
		'fieldContent' => $file,
		'fieldId' => $fileId,
	])
);
