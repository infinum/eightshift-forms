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
$componentJsSingleSubmitClass = $manifest['componentJsSingleSubmitClass'] ?? '';

$textareaName = Components::checkAttr('textareaName', $attributes, $manifest);
$textareaValue = Components::checkAttr('textareaValue', $attributes, $manifest);
$textareaPlaceholder = Components::checkAttr('textareaPlaceholder', $attributes, $manifest);
$textareaIsDisabled = Components::checkAttr('textareaIsDisabled', $attributes, $manifest);
$textareaIsReadOnly = Components::checkAttr('textareaIsReadOnly', $attributes, $manifest);
$textareaIsRequired = Components::checkAttr('textareaIsRequired', $attributes, $manifest);
$textareaTracking = Components::checkAttr('textareaTracking', $attributes, $manifest);
$textareaAttrs = Components::checkAttr('textareaAttrs', $attributes, $manifest);
$textareaIsMonospace = Components::checkAttr('textareaIsMonospace', $attributes, $manifest);
$textareaSingleSubmit = Components::checkAttr('textareaSingleSubmit', $attributes, $manifest);
$textareaSaveAsJson = Components::checkAttr('textareaSaveAsJson', $attributes, $manifest);

// Fix for getting attribute that is part of the child component.
$textareaFieldLabel = $attributes[Components::getAttrKey('textareaFieldLabel', $attributes, $manifest)] ?? '';

$textareaClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($textareaIsMonospace, $componentClass, '', 'monospace'),
	Components::selector($textareaSingleSubmit, $componentJsSingleSubmitClass),
]);

if ($textareaTracking) {
	$textareaAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['tracking']] = esc_attr($textareaTracking);
}

if ($textareaSaveAsJson) {
	$textareaAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['saveAsJson']] = esc_attr($textareaSaveAsJson);
}

if ($textareaPlaceholder) {
	$textareaAttrs['placeholder'] = esc_attr($textareaPlaceholder);
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
	>' . apply_filters('the_content', $textareaValue) . '</textarea>
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
