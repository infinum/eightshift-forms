<?php

/**
 * Template for the Textarea Component.
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

$textareaId = Components::checkAttr('textareaId', $attributes, $manifest);
$textareaName = Components::checkAttr('textareaName', $attributes, $manifest);
$textareaValue = Components::checkAttr('textareaValue', $attributes, $manifest);
$textareaPlaceholder = Components::checkAttr('textareaPlaceholder', $attributes, $manifest);
$textareaIsDisabled = Components::checkAttr('textareaIsDisabled', $attributes, $manifest);
$textareaIsReadOnly = Components::checkAttr('textareaIsReadOnly', $attributes, $manifest);
$textareaTracking = Components::checkAttr('textareaTracking', $attributes, $manifest);
$textareaUseCustom = Components::checkAttr('textareaUseCustom', $attributes, $manifest);
$textareaAttrs = Components::checkAttr('textareaAttrs', $attributes, $manifest);

$isCustomTextarea = !apply_filters(
	Blocks::BLOCKS_OPTION_CHECKBOX_IS_CHECKED_FILTER_NAME,
	SettingsGeneral::SETTINGS_GENERAL_CUSTOM_OPTIONS_TEXTAREA,
	SettingsGeneral::SETTINGS_GENERAL_CUSTOM_OPTIONS_KEY
);

// Fix for getting attribute that is part of the child component.
$textareaFieldLabel = $attributes[Components::getAttrKey('textareaFieldLabel', $attributes, $manifest)] ?? '';

$textareaClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($isCustomTextarea && $textareaUseCustom, $componentClass, '', 'custom'),
]);

if ($isCustomTextarea && $textareaUseCustom) {
	$additionalFieldClass .= Components::selector($componentClass, "{$componentClass}-is-custom");
	$additionalFieldClass .= ' ' . Components::selector($componentCustomJsClass, $componentCustomJsClass);
}

if ($textareaTracking) {
	$textareaAttrs['data-tracking'] = esc_attr($textareaTracking);
}

if ($textareaPlaceholder) {
	$textareaAttrs['placeholder'] = esc_attr($textareaPlaceholder);
}

$textareaAttrsOutput = '';
if ($textareaAttrs) {
	foreach ($textareaAttrs as $key => $value) {
		$textareaAttrsOutput .= \wp_kses_post(" {$key}='" . $value . "'");
	}
}

// Additional content filter.
$additionalContent = '';
if (has_filter(Filters::FILTER_BLOCK_TEXTAREA_ADDITIONAL_CONTENT_NAME)) {
	$additionalContent = apply_filters(Filters::FILTER_BLOCK_TEXTAREA_ADDITIONAL_CONTENT_NAME, $attributes ?? []);
}

$textarea = '<textarea
		class="' . esc_attr($textareaClass) . '"
		name="' . esc_attr($textareaName) . '"
		id="' . esc_attr($textareaId) . '"
		' . disabled($textareaIsDisabled, true, false) . '
		' . readonly($textareaIsReadOnly, true, false) . '
		' . $textareaAttrsOutput . '
	>' . \apply_filters('the_content', $textareaValue) . '</textarea>
	' . $additionalContent . '
';

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $textarea,
			'fieldId' => $textareaId,
			'fieldName' => $textareaName,
			'fieldDisabled' => !empty($textareaIsDisabled),
		]),
		[
			'additionalFieldClass' => $additionalFieldClass,
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
