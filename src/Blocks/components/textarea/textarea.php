<?php

/**
 * Template for the Textarea Component.
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

$textareaName = Components::checkAttr('textareaName', $attributes, $manifest);
if (!$textareaName) {
	return;
}
$textareaValue = Components::checkAttr('textareaValue', $attributes, $manifest);
$textareaPlaceholder = Components::checkAttr('textareaPlaceholder', $attributes, $manifest);
$textareaIsDisabled = Components::checkAttr('textareaIsDisabled', $attributes, $manifest);
$textareaIsReadOnly = Components::checkAttr('textareaIsReadOnly', $attributes, $manifest);
$textareaIsRequired = Components::checkAttr('textareaIsRequired', $attributes, $manifest);
$textareaTracking = Components::checkAttr('textareaTracking', $attributes, $manifest);
$textareaAttrs = Components::checkAttr('textareaAttrs', $attributes, $manifest);
$textareaIsMonospace = Components::checkAttr('textareaIsMonospace', $attributes, $manifest);
$textareaSaveAsJson = Components::checkAttr('textareaSaveAsJson', $attributes, $manifest);
$textareaTypeCustom = Components::checkAttr('textareaTypeCustom', $attributes, $manifest);
$textareaFieldAttrs = Components::checkAttr('textareaFieldAttrs', $attributes, $manifest);
$textareaSize = Components::checkAttr('textareaSize', $attributes, $manifest);
$textareaLimitHeight = Components::checkAttr('textareaLimitHeight', $attributes, $manifest);
$textareaIsPreventSubmit = Components::checkAttr('textareaIsPreventSubmit', $attributes, $manifest);
$textareaUseLabelAsPlaceholder = Components::checkAttr('textareaUseLabelAsPlaceholder', $attributes, $manifest);

// Fix for getting attribute that is part of the child component.
$textareaHideLabel = false;
$textareaFieldLabel = $attributes[Components::getAttrKey('textareaFieldLabel', $attributes, $manifest)] ?? '';

$textareaClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($textareaIsMonospace, $componentClass, '', 'monospace'),
	Components::selector($textareaSize, $componentClass, 'size', $textareaSize),
	Components::selector($textareaLimitHeight, $componentClass, '', 'limit-height'),
]);

if ($textareaSaveAsJson) {
	$textareaAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['saveAsJson']] = esc_attr($textareaSaveAsJson);
}

// Set to use in settings for preventing field submit.
if ($textareaIsPreventSubmit) {
	$textareaFieldAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['fieldPreventSubmit']] = esc_attr($textareaIsPreventSubmit);
}

if ($textareaPlaceholder) {
	$textareaAttrs['placeholder'] = esc_attr($textareaPlaceholder);
}

if ($textareaUseLabelAsPlaceholder) {
	$textareaAttrs['placeholder'] = esc_attr($textareaFieldLabel);
	$textareaHideLabel = true;
}

$textareaAttrsOutput = '';
if ($textareaAttrs) {
	foreach ($textareaAttrs as $key => $value) {
		$textareaAttrsOutput .= wp_kses_post(" {$key}='" . $value . "'");
	}
}

// Additional content filter.
$additionalContent = Helper::getBlockAdditionalContentViaFilter('textarea', $attributes);

$textarea = '<textarea
		class="' . esc_attr($textareaClass) . '"
		name="' . esc_attr($textareaName) . '"
		id="' . esc_attr($textareaName) . '"
		' . disabled($textareaIsDisabled, true, false) . '
		' . wp_readonly($textareaIsReadOnly, true, false) . '
		' . $textareaAttrsOutput . '
	>' . wp_kses_post($textareaValue) . '</textarea>
	' . $additionalContent . '
';

echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $textarea,
			'fieldId' => $textareaName,
			'fieldName' => $textareaName,
			'fieldIsRequired' => $textareaIsRequired,
			'fieldDisabled' => !empty($textareaIsDisabled),
			'fieldTypeCustom' => $textareaTypeCustom ?: 'textarea', // phpcs:ignore WordPress.PHP.DisallowShortTernary.Found
			'fieldTracking' => $textareaTracking,
			'fieldHideLabel' => $textareaHideLabel,
			'fieldConditionalTags' => Components::render(
				'conditional-tags',
				Components::props('conditionalTags', $attributes)
			),
			'fieldAttrs' => $textareaFieldAttrs,
		]),
		[
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
