<?php

/**
 * Template for the Submit Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Helpers\Helper;

$manifest = Components::getManifest(__DIR__);
$manifestUtils = Components::getComponent('utils');
$manifestTypeInternal = Components::getSettings()['typeInternal'];

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$submitName = Components::checkAttr('submitName', $attributes, $manifest);
$submitValue = Components::checkAttr('submitValue', $attributes, $manifest);
$submitIsDisabled = Components::checkAttr('submitIsDisabled', $attributes, $manifest);
$submitTracking = Components::checkAttr('submitTracking', $attributes, $manifest);
$submitAttrs = Components::checkAttr('submitAttrs', $attributes, $manifest);
$submitServerSideRender = Components::checkAttr('submitServerSideRender', $attributes, $manifest);
$submitUniqueId = Components::checkAttr('submitUniqueId', $attributes, $manifest);
$submitIcon = Components::checkAttr('submitIcon', $attributes, $manifest);
$submitVariant = Components::checkAttr('submitVariant', $attributes, $manifest);
$submitButtonComponent = Components::checkAttr('submitButtonComponent', $attributes, $manifest);
$submitButtonAsLink = Components::checkAttr('submitButtonAsLink', $attributes, $manifest);
$submitButtonAsLinkUrl = Components::checkAttr('submitButtonAsLinkUrl', $attributes, $manifest);

$submitClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($submitIcon, $componentClass, '', 'with-icon'),
	Components::selector($submitVariant, $componentClass, '', $submitVariant),
]);

// Additional content filter.
$additionalContent = Helper::getBlockAdditionalContentViaFilter('submit', $attributes);

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

echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $button,
			'fieldId' => $submitName,
			'fieldUseError' => false,
			'fieldTypeInternal' => $manifestTypeInternal['submit'],
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
