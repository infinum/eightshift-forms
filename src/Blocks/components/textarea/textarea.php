<?php

/**
 * Template for the Textarea Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\Blocks;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Rest\Routes\AbstractBaseRoute;
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
$textareaIsRequired = Components::checkAttr('textareaIsRequired', $attributes, $manifest);
$textareaTracking = Components::checkAttr('textareaTracking', $attributes, $manifest);
$textareaUseCustom = Components::checkAttr('textareaUseCustom', $attributes, $manifest);
$textareaAttrs = Components::checkAttr('textareaAttrs', $attributes, $manifest);
$textareaIsMonospace = Components::checkAttr('textareaIsMonospace', $attributes, $manifest);

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
	Components::selector($textareaIsMonospace, $componentClass, '', 'monospace'),
]);

if ($isCustomTextarea && $textareaUseCustom) {
	$additionalFieldClass .= Components::selector($componentClass, "{$componentClass}-is-custom");
	$additionalFieldClass .= ' ' . Components::selector($componentCustomJsClass, $componentCustomJsClass);
}

if ($textareaTracking) {
	$textareaAttrs[AbstractBaseRoute::CUSTOM_FORM_DATA_ATTRIBUTES['tracking']] = esc_attr($textareaTracking);
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
$additionalContent = '';
$filterName = Filters::getBlockFilterName('textarea', 'additionalContent');
if (has_filter($filterName)) {
	$additionalContent = apply_filters($filterName, $attributes ?? []);
}

$isWpFiveNine = is_wp_version_compatible('5.9');
$textarea = '<textarea
		class="' . esc_attr($textareaClass) . '"
		name="' . esc_attr($textareaName) . '"
		id="' . esc_attr($textareaId) . '"
		' . disabled($textareaIsDisabled, true, false) . '
		' . ($isWpFiveNine ? wp_readonly($textareaIsReadOnly, true, false) : readonly($textareaIsReadOnly, true, false)) . /* @phpstan-ignore-line */ ' 
		' . $textareaAttrsOutput . '
	>' . apply_filters('the_content', $textareaValue) . '</textarea>
	' . $additionalContent . '
';

echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $textarea,
			'fieldId' => $textareaId,
			'fieldName' => $textareaName,
			'fieldIsRequired' => $textareaIsRequired,
			'fieldDisabled' => !empty($textareaIsDisabled),
		]),
		[
			'additionalFieldClass' => $additionalFieldClass,
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
