<?php

/**
 * Template for the Select Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\Blocks;
use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;
use EightshiftForms\Hooks\Filters;
use EightshiftForms\Settings\Settings\SettingsGeneral;

$manifest = Components::getManifest(__DIR__);

$componentName = $manifest['componentName'] ?? '';
$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$componentJsSingleSubmitClass = $manifest['componentJsSingleSubmitClass'] ?? '';
$componentCustomJsClass = $manifest['componentCustomJsClass'] ?? '';
$additionalFieldClass = $attributes['additionalFieldClass'] ?? '';

$selectId = Components::checkAttr('selectId', $attributes, $manifest);
$selectName = Components::checkAttr('selectName', $attributes, $manifest);
$selectIsDisabled = Components::checkAttr('selectIsDisabled', $attributes, $manifest);
$selectOptions = Components::checkAttr('selectOptions', $attributes, $manifest);
$selectTracking = Components::checkAttr('selectTracking', $attributes, $manifest);
$selectSingleSubmit = Components::checkAttr('selectSingleSubmit', $attributes, $manifest);
$selectUseCustom = Components::checkAttr('selectUseCustom', $attributes, $manifest);

$isCustomSelect = !apply_filters(
	Blocks::BLOCKS_OPTION_CHECKBOX_IS_CHECKED_FILTER_NAME,
	SettingsGeneral::SETTINGS_GENERAL_CUSTOM_OPTIONS_SELECT,
	SettingsGeneral::SETTINGS_GENERAL_CUSTOM_OPTIONS_KEY
);

// Fix for getting attribute that is part of the child component.
$selectFieldLabel = $attributes[Components::getAttrKey('selectFieldLabel', $attributes, $manifest)] ?? '';

$selectClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($selectSingleSubmit, $componentJsSingleSubmitClass),
	Components::selector($isCustomSelect && $selectUseCustom, $componentClass, '', 'custom'),
]);

if ($isCustomSelect && $selectUseCustom) {
	$additionalFieldClass .= Components::selector($componentClass, "{$componentClass}-is-custom");
	$additionalFieldClass .= ' ' . Components::selector($componentCustomJsClass, $componentCustomJsClass);
}

$selectAttrs = [];
if ($selectTracking) {
	$selectAttrs['data-tracking'] = esc_attr($selectTracking);
}

$selectAttrsOutput = '';
if ($selectAttrs) {
	foreach ($selectAttrs as $key => $value) {
		$selectAttrsOutput .= \wp_kses_post(" {$key}='" . $value . "'");
	}
}

// Additional content filter.
$additionalContent = '';
if (has_filter(Filters::FILTER_BLOCK_SELECT_ADDITIONAL_CONTENT_NAME)) {
	$attributes['selectOptions'] = Helper::convetInnerBlocksToArray($attributes['selectOptions'] ?? '', $componentName);
	$additionalContent = apply_filters(Filters::FILTER_BLOCK_SELECT_ADDITIONAL_CONTENT_NAME, $attributes ?? []);
}

$select = '
	<select
		class="' . esc_attr($selectClass) . '"
		name="' . esc_attr($selectName) . '"
		id="' . esc_attr($selectId) . '"
		' . disabled($selectIsDisabled, true, false) . '
		' . $selectAttrsOutput . '
	>
		' . $selectOptions . '
	</select>
	' . $additionalContent . '
';

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $select,
			'fieldId' => $selectId,
			'fieldName' => $selectName,
			'fieldDisabled' => !empty($selectIsDisabled),
		]),
		[
			'additionalFieldClass' => $additionalFieldClass,
			'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
