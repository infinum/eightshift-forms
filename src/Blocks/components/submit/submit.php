<?php

/**
 * Template for the Submit Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;
use EightshiftForms\Helpers\GeneralHelpers;
use EightshiftForms\Helpers\UtilsHelper;

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$submitName = Helpers::checkAttr('submitName', $attributes, $manifest);
$submitValue = Helpers::checkAttr('submitValue', $attributes, $manifest);
$submitIsDisabled = Helpers::checkAttr('submitIsDisabled', $attributes, $manifest);
$submitTracking = Helpers::checkAttr('submitTracking', $attributes, $manifest);
$submitAttrs = Helpers::checkAttr('submitAttrs', $attributes, $manifest);
$submitIcon = Helpers::checkAttr('submitIcon', $attributes, $manifest);
$submitVariant = Helpers::checkAttr('submitVariant', $attributes, $manifest);
$submitButtonComponent = Helpers::checkAttr('submitButtonComponent', $attributes, $manifest);
$submitButtonAsLink = Helpers::checkAttr('submitButtonAsLink', $attributes, $manifest);
$submitButtonAsLinkUrl = Helpers::checkAttr('submitButtonAsLinkUrl', $attributes, $manifest);
$submitButtonTwParent = Helpers::checkAttr('submitButtonTwParent', $attributes, $manifest);
$submitTwSelectorsData = Helpers::checkAttr('submitTwSelectorsData', $attributes, $manifest);

$twClasses = FormsHelper::getTwSelectors($submitTwSelectorsData, [$submitButtonTwParent]);

$submitClass = Helpers::classnames([
	FormsHelper::getTwBase($twClasses, $submitButtonTwParent, $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
	Helpers::selector($submitIcon, $componentClass, '', 'with-icon'),
	Helpers::selector($submitVariant, $componentClass, '', $submitVariant),
]);

// Additional content filter.
$additionalContent = GeneralHelpers::getBlockAdditionalContentViaFilter('submit', $attributes);

$submitIconContent = '';
if ($submitIcon) {
	$submitIconContent = $submitIcon;
}

$button = '
	<button
		class="' . esc_attr($submitClass) . '"
		' . disabled($submitIsDisabled, true, false) . '
	><span class="' . esc_attr(FormsHelper::getTwPart($twClasses, $submitButtonTwParent, 'inner', "{$componentClass}__inner")) . '">' . $submitIconContent . ' ' . esc_html($submitValue) . '</span></button>
	' . $additionalContent . '
';

if ($submitButtonAsLink) {
	$submitLinkClass = Helpers::classnames([
		Helpers::selector($submitIsDisabled, UtilsHelper::getStateSelector('isDisabled')),
	]);

	$button = '
	<a
		href="' . esc_url($submitButtonAsLinkUrl) . '"
		class="' . esc_attr("{$submitClass} {$submitLinkClass}") . '"
	><span class="' . esc_attr(FormsHelper::getTwPart($twClasses, $submitButtonTwParent, 'inner', "{$componentClass}__inner")) . '">' . $submitIconContent . ' ' . esc_html($submitValue) . '</span></a>
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
			'fieldTwSelectorsData' => $submitTwSelectorsData,
			'fieldUseError' => false,
			'fieldTypeInternal' => FormsHelper::getStateFieldType('submit'),
			'fieldDisabled' => !empty($submitIsDisabled),
			'fieldTracking' => $submitTracking,
			'fieldAttrs' => $submitAttrs,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
