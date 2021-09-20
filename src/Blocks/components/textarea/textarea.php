<?php

/**
 * Template for the Textarea Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$textareaName = Components::checkAttr('textareaName', $attributes, $manifest);
$textareaValue = Components::checkAttr('textareaValue', $attributes, $manifest);
$textareaId = Components::checkAttr('textareaId', $attributes, $manifest);
$textareaPlaceholder = Components::checkAttr('textareaPlaceholder', $attributes, $manifest);
$textareaIsDisabled = Components::checkAttr('textareaIsDisabled', $attributes, $manifest);
$textareaIsReadOnly = Components::checkAttr('textareaIsReadOnly', $attributes, $manifest);
$textareaIsRequired = Components::checkAttr('textareaIsRequired', $attributes, $manifest);
$textareaTracking = Components::checkAttr('textareaTracking', $attributes, $manifest);

$textareaClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
]);

$textareaIsDisabled = $textareaIsDisabled ? 'disabled' : '';
$textareaIsReadOnly = $textareaIsReadOnly ? 'readonly' : '';

if (empty($textareaId)) {
	$textareaId = $textareaName;
}


$textarea = '<textarea
		class="' . esc_attr($textareaClass) . '"
		name="' . esc_attr($textareaName) . '"
		id="' . esc_attr($textareaId) . '"
		placeholder="' . esc_attr($textareaPlaceholder) . '"
		data-validation-required="' . $textareaIsRequired . '"
		data-tracking="' . $textareaTracking . '"
		' . $textareaIsDisabled . '
		' . $textareaIsReadOnly . '
	>' . esc_attr($textareaValue) . '</textarea>
';

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'field',
	Components::props('field', $attributes, [
		'fieldContent' => $textarea,
		'fieldId' => $textareaId,
	])
);


