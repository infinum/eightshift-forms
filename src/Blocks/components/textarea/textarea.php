<?php

/**
 * Template for the Textarea Component.
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

$textareaId = Components::checkAttr('textareaId', $attributes, $manifest);
$textareaName = Components::checkAttr('textareaName', $attributes, $manifest);
$textareaValue = Components::checkAttr('textareaValue', $attributes, $manifest);
$textareaPlaceholder = Components::checkAttr('textareaPlaceholder', $attributes, $manifest);
$textareaIsDisabled = Components::checkAttr('textareaIsDisabled', $attributes, $manifest);
$textareaIsReadOnly = Components::checkAttr('textareaIsReadOnly', $attributes, $manifest);
$textareaTracking = Components::checkAttr('textareaTracking', $attributes, $manifest);

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
	Components::selector($isCustomTextarea, $componentClass, '', 'custom'),
]);

if ($isCustomTextarea) {
	$additionalFieldClass .= Components::selector($componentClass, "{$componentClass}-is-custom");
}

$attrsOutput = '';
if ($textareaTracking) {
	$attrsOutput .= " data-tracking='" . esc_attr($textareaTracking) . "'";
}

if ($textareaPlaceholder) {
	$attrsOutput .= " placeholder='" . esc_attr($textareaPlaceholder) . "'";
}

$textarea = '<textarea
		class="' . esc_attr($textareaClass) . '"
		name="' . esc_attr($textareaName) . '"
		id="' . esc_attr($textareaId) . '"
		' . disabled($textareaIsDisabled, true, false) . '
		' . readonly($textareaIsReadOnly, true, false) . '
		' . $attrsOutput . '
	>' . \apply_filters('the_content', $textareaValue) . '</textarea>
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
