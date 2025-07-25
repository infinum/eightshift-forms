<?php

/**
 * Template for the file Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$fileName = Helpers::checkAttr('fileName', $attributes, $manifest);
if (!$fileName) {
	return;
}

$fileIsRequired = Helpers::checkAttr('fileIsRequired', $attributes, $manifest);
$fileIsMultiple = Helpers::checkAttr('fileIsMultiple', $attributes, $manifest);
$fileTracking = Helpers::checkAttr('fileTracking', $attributes, $manifest);
$fileCustomInfoText = Helpers::checkAttr('fileCustomInfoText', $attributes, $manifest);
$fileCustomInfoTextUse = Helpers::checkAttr('fileCustomInfoTextUse', $attributes, $manifest);
$fileCustomInfoButtonText = Helpers::checkAttr('fileCustomInfoButtonText', $attributes, $manifest);
$fileTypeCustom = Helpers::checkAttr('fileTypeCustom', $attributes, $manifest);
$fileAttrs = Helpers::checkAttr('fileAttrs', $attributes, $manifest);
$fileFieldAttrs = Helpers::checkAttr('fileFieldAttrs', $attributes, $manifest);
$fileIsDisabled = Helpers::checkAttr('fileIsDisabled', $attributes, $manifest);
$fileTwSelectorsData = Helpers::checkAttr('fileTwSelectorsData', $attributes, $manifest);

$fileId = $fileName . '-' . Helpers::getUnique();

// Fix for getting attribute that is part of the child component.
$fileFieldLabel = $attributes[Helpers::getAttrKey('fileFieldLabel', $attributes, $manifest)] ?? '';

$twClasses = FormsHelper::getTwSelectors($fileTwSelectorsData, ['file']);

$fileClass = Helpers::classnames([
	FormsHelper::getTwBase($twClasses, 'file', $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
]);

$fileIsMultiple = $fileIsMultiple ? 'multiple' : '';

$customFile = '';

$infoText = !empty($fileCustomInfoText) ? $fileCustomInfoText : __('Drag and drop files here', 'eighitshift-forms');
$infoButton = !empty($fileCustomInfoButtonText) ? $fileCustomInfoButtonText : __('Add files', 'eighitshift-forms');

$infoTextContent = '';
if ($fileCustomInfoTextUse) {
	$infoTextContent .= '<div class="' . esc_attr(FormsHelper::getTwPart($twClasses, 'file', 'info', "{$componentClass}__info")) . '">' . wp_kses_post($infoText) . '</div>';
}

$filter = UtilsHooksHelper::getFilterName(['block', 'file', 'infoAdditionalContent']);
if (has_filter($filter)) {
	$infoTextContent .= apply_filters($filter, '', $attributes);
}

$infoTextContent .= '<div class="' . esc_attr(FormsHelper::getTwPart($twClasses, 'file', 'button', "{$componentClass}__button")) . '">' . esc_html($infoButton) . '</div>';

$jsSelector = UtilsHelper::getStateSelector('fileButton');

$fileButtonAttrs['role'] = 'button';

$customFile = '
	<a
		href="#"
		class="' . esc_attr(FormsHelper::getTwPart($twClasses, 'file', 'custom-wrap', "{$componentClass}__custom-wrap {$jsSelector}")) . '"
		' . Helpers::getAttrsOutput($fileButtonAttrs) . '
	>
		' . $infoTextContent . '
	</a>
';

// Additional content filter.
$additionalContent = UtilsGeneralHelper::getBlockAdditionalContentViaFilter('file', $attributes);

if ($fileIsRequired) {
	$fileAttrs['aria-required'] = 'true';
}

$fileAttrs['aria-invalid'] = 'false';


$file = '
	<input
		class="' . esc_attr($fileClass) . '"
		name="' . esc_attr($fileName) . '"
		aria-hidden="true"
		tabindex="-1"
		id="' . esc_attr($fileId) . '"
		' . disabled($fileIsDisabled, true, false) . '
		type="file"
		' . $fileIsMultiple . '
		' . Helpers::getAttrsOutput($fileAttrs) . '
	/>
	' . $customFile . '
	' . $additionalContent . '
';

echo Helpers::render(
	'field',
	array_merge(
		Helpers::props('field', $attributes, [
			'fieldContent' => $file,
			'fieldId' => $fileId,
			'fieldName' => $fileName,
			'fieldTwSelectorsData' => $fileTwSelectorsData,
			'fieldTypeInternal' => FormsHelper::getStateFieldType('file'),
			'fieldDisabled' => !empty($fileIsDisabled),
			'fieldIsRequired' => $fileIsRequired,
			'fieldTypeCustom' => $fileTypeCustom ?: 'file', // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
			'fieldTracking' => $fileTracking,
			'fieldConditionalTags' => Helpers::render(
				'conditional-tags',
				Helpers::props('conditionalTags', $attributes)
			),
			'fieldAttrs' => array_merge($fileFieldAttrs, [
				'aria-labelledby' => $fileId,
			]),
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
