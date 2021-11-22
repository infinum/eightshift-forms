<?php

/**
 * Template for the file Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\Blocks;
use EightshiftForms\Helpers\Components;
use EightshiftForms\Settings\Settings\SettingsGeneral;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;
$additionalFieldClass = $attributes['additionalFieldClass'] ?? '';

$fileId = Components::checkAttr('fileId', $attributes, $manifest);
$fileName = Components::checkAttr('fileName', $attributes, $manifest);
$fileIsMultiple = Components::checkAttr('fileIsMultiple', $attributes, $manifest);
$fileTracking = Components::checkAttr('fileTracking', $attributes, $manifest);

$isCustomFile = !apply_filters(
	Blocks::BLOCKS_OPTION_CHECKBOX_IS_CHECKED_FILTER_NAME,
	SettingsGeneral::SETTINGS_GENERAL_CUSTOM_OPTIONS_FILE,
	SettingsGeneral::SETTINGS_GENERAL_CUSTOM_OPTIONS_KEY
);

// Fix for getting attribute that is part of the child component.
$fileFieldLabel = $attributes[Components::getAttrKey('fileFieldLabel', $attributes, $manifest)] ?? '';

$fileClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($isCustomFile, $componentClass, '', 'custom'),
]);

$fileIsMultiple = $fileIsMultiple ? 'multiple' : '';

$customFile = '';

if ($isCustomFile) {
	$customFile = '
		<div class="' . esc_attr("{$componentClass}__custom-wrap") . '">
			<div class="' . esc_attr("{$componentClass}__info") . '">' . esc_attr__('Drag and drop files here', 'eighitshift-forms') . '</div>
			<a href="#" class="' . esc_attr("{$componentClass}__button") . '">' . esc_attr__('Add files', 'eighitshift-forms') . '</a>
		</div>
	';

	$additionalFieldClass .= Components::selector($componentClass, "{$componentClass}-is-custom");
}

$attrsOutput = '';
if ($fileTracking) {
	$attrsOutput .= " data-tracking='" . esc_attr($fileTracking) . "'";
}

$file = '
	<input
		class="' . esc_attr($fileClass) . '"
		name="' . esc_attr($fileName) . '"
		id="' . esc_attr($fileId) . '"
		type="file"
		' . $fileIsMultiple . '
		' . $attrsOutput . '
	/>
	' . $customFile . '
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
			'additionalFieldClass' => $additionalFieldClass,
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
