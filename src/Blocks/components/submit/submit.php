<?php

/**
 * Template for the Submit Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;

$manifest = Helpers::getManifestByDir(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$submitName = Helpers::checkAttr('submitName', $attributes, $manifest);
$submitValue = Helpers::checkAttr('submitValue', $attributes, $manifest);
$submitIsDisabled = Helpers::checkAttr('submitIsDisabled', $attributes, $manifest);
$submitTracking = Helpers::checkAttr('submitTracking', $attributes, $manifest);
$submitAttrs = Helpers::checkAttr('submitAttrs', $attributes, $manifest);
$submitServerSideRender = Helpers::checkAttr('submitServerSideRender', $attributes, $manifest);
$submitUniqueId = Helpers::checkAttr('submitUniqueId', $attributes, $manifest);
$submitIcon = Helpers::checkAttr('submitIcon', $attributes, $manifest);
$submitVariant = Helpers::checkAttr('submitVariant', $attributes, $manifest);
$submitButtonComponent = Helpers::checkAttr('submitButtonComponent', $attributes, $manifest);
$submitButtonAsLink = Helpers::checkAttr('submitButtonAsLink', $attributes, $manifest);
$submitButtonAsLinkUrl = Helpers::checkAttr('submitButtonAsLinkUrl', $attributes, $manifest);

$submitClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
	Helpers::selector($submitIcon, $componentClass, '', 'with-icon'),
	Helpers::selector($submitVariant, $componentClass, '', $submitVariant),
]);

// Additional content filter.
$additionalContent = UtilsGeneralHelper::getBlockAdditionalContentViaFilter('submit', $attributes);

$submitIconContent = '';
if ($submitIcon) {
	$submitIconContent = $submitIcon;
}

$button = '
	<button
		class="' . esc_attr($submitClass) . '"
		' . disabled($submitIsDisabled, true, false) . '
	><span class="' . $componentClass . '__inner">' . $submitIconContent . ' ' . esc_html($submitValue) . '</span></button>
	' . $additionalContent . '
';

if ($submitButtonAsLink) {
	$button = '
	<a
		href="' . esc_url($submitButtonAsLinkUrl) . '"
		class="' . esc_attr($submitClass) . '"
	><span class="' . $componentClass . '__inner">' . $submitIconContent . ' ' . esc_html($submitValue) . '</span></a>
	' . $additionalContent . '
	';
}

// Used if you want to provide external component for button.
if ($submitButtonComponent) {
	$button = "{$submitButtonComponent}{$additionalContent}";
}

echo Helpers::render(
	'field',
	array_merge(
		Helpers::props('field', $attributes, [
			'fieldContent' => $button,
			'fieldId' => $submitName,
			'fieldUseError' => false,
			'fieldTypeInternal' => FormsHelper::getStateFieldType('submit'),
			'fieldDisabled' => !empty($submitIsDisabled),
			'fieldTracking' => $submitTracking,
			'fieldUniqueId' => $submitUniqueId,
			'fieldAttrs' => $submitAttrs,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? '',
			'blockSsr' => $submitServerSideRender,
		]
	)
);
