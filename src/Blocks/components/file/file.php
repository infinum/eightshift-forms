<?php

/**
 * Template for the file Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\Blocks;
use EightshiftForms\Helpers\Components;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\Settings\SettingsGeneral;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$componentCustomJsClass = $manifest['componentCustomJsClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;
$additionalFieldClass = $attributes['additionalFieldClass'] ?? '';

$fileId = Components::checkAttr('fileId', $attributes, $manifest);
$fileName = Components::checkAttr('fileName', $attributes, $manifest);
$fileIsMultiple = Components::checkAttr('fileIsMultiple', $attributes, $manifest);
$fileTracking = Components::checkAttr('fileTracking', $attributes, $manifest);
$fileCustomInfoText = Components::checkAttr('fileCustomInfoText', $attributes, $manifest);
$fileCustomInfoTextUse = Components::checkAttr('fileCustomInfoTextUse', $attributes, $manifest);
$fileCustomInfoButtonText = Components::checkAttr('fileCustomInfoButtonText', $attributes, $manifest);
$fileUseCustom = Components::checkAttr('fileUseCustom', $attributes, $manifest);
$fileAttrs = Components::checkAttr('fileAttrs', $attributes, $manifest);

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
	Components::selector($isCustomFile && $fileUseCustom, $componentClass, '', 'custom'),
]);

$fileIsMultiple = $fileIsMultiple ? 'multiple' : '';

$customFile = '';

if ($isCustomFile && $fileUseCustom) {
	$infoText = !empty($fileCustomInfoText) ? $fileCustomInfoText : __('Drag and drop files here', 'eighitshift-forms');
	$infoButton = !empty($fileCustomInfoButtonText) ? $fileCustomInfoButtonText : __('Add files', 'eighitshift-forms');

	$infoTextContent = '<div class="' . esc_attr("{$componentClass}__info") . '">' . esc_html($infoText) . '</div>';
	if (!$fileCustomInfoTextUse) {
		$infoTextContent = '';
	}

	$infoButtonContent = '<a href="#" class="' . esc_attr("{$componentClass}__button") . '">' . esc_html($infoButton) . '</a>';

	$customFile = '
		<div class="' . esc_attr("{$componentClass}__custom-wrap") . '">
			' . $infoTextContent . '
			' . $infoButtonContent . '
		</div>
	';

	$additionalFieldClass .= Components::selector($componentClass, "{$componentClass}-is-custom");
	$additionalFieldClass .= ' ' . Components::selector($componentCustomJsClass, $componentCustomJsClass);
}

if ($fileTracking) {
	$fileAttrs['data-tracking'] = esc_attr($fileTracking);
}

$fileAttrsOutput = '';
if ($fileAttrs) {
	foreach ($fileAttrs as $key => $value) {
		$fileAttrsOutput .= \wp_kses_post(" {$key}='" . $value . "'");
	}
}

// Additional content filter.
$additionalContent = '';
$filterName = Filters::getBlockFilterName('file', 'additionalContent');
if (has_filter($filterName)) {
	$additionalContent = apply_filters($filterName, $attributes ?? []);
}

$file = '
	<input
		class="' . esc_attr($fileClass) . '"
		name="' . esc_attr($fileName) . '"
		id="' . esc_attr($fileId) . '"
		type="file"
		' . $fileIsMultiple . '
		' . $fileAttrsOutput . '
	/>
	' . $customFile . '
	' . $additionalContent . '
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
