<?php

/**
 * Template for the file Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Helper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$fileName = Components::checkAttr('fileName', $attributes, $manifest);
$fileIsRequired = Components::checkAttr('fileIsRequired', $attributes, $manifest);
$fileIsMultiple = Components::checkAttr('fileIsMultiple', $attributes, $manifest);
$fileTracking = Components::checkAttr('fileTracking', $attributes, $manifest);
$fileCustomInfoText = Components::checkAttr('fileCustomInfoText', $attributes, $manifest);
$fileCustomInfoTextUse = Components::checkAttr('fileCustomInfoTextUse', $attributes, $manifest);
$fileCustomInfoButtonText = Components::checkAttr('fileCustomInfoButtonText', $attributes, $manifest);
$fileAttrs = Components::checkAttr('fileAttrs', $attributes, $manifest);

// Fix for getting attribute that is part of the child component.
$fileFieldLabel = $attributes[Components::getAttrKey('fileFieldLabel', $attributes, $manifest)] ?? '';

$fileClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
]);

$fileIsMultiple = $fileIsMultiple ? 'multiple' : '';

$customFile = '';

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

if ($fileTracking) {
	$fileAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['tracking']] = esc_attr($fileTracking);
}

$fileAttrsOutput = '';
if ($fileAttrs) {
	foreach ($fileAttrs as $key => $value) {
		$fileAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

// Additional content filter.
$additionalContent = Helper::getBlockAdditionalContentViaFilter('file', $attributes);

$file = '
	<input
		class="' . esc_attr($fileClass) . '"
		name="' . esc_attr($fileName) . '"
		id="' . esc_attr($fileName) . '"
		type="file"
		' . $fileIsMultiple . '
		' . $fileAttrsOutput . '
	/>
	' . $customFile . '
	' . $additionalContent . '
';

echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $file,
			'fieldId' => $fileName,
			'fieldName' => $fileName,
			'fieldIsRequired' => $fileIsRequired,
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
