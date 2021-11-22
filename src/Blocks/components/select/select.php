<?php

/**
 * Template for the Select Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\Blocks;
use EightshiftForms\Helpers\Components;
use EightshiftForms\Settings\Settings\SettingsGeneral;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$componentJsSingleSubmitClass = $manifest['componentJsSingleSubmitClass'] ?? '';
$additionalFieldClass = $attributes['additionalFieldClass'] ?? '';

$selectId = Components::checkAttr('selectId', $attributes, $manifest);
$selectName = Components::checkAttr('selectName', $attributes, $manifest);
$selectIsDisabled = Components::checkAttr('selectIsDisabled', $attributes, $manifest);
$selectOptions = Components::checkAttr('selectOptions', $attributes, $manifest);
$selectTracking = Components::checkAttr('selectTracking', $attributes, $manifest);
$selectSingleSubmit = Components::checkAttr('selectSingleSubmit', $attributes, $manifest);

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
	Components::selector($isCustomSelect, $componentClass, '', 'custom'),
]);

if ($isCustomSelect) {
	$additionalFieldClass .= Components::selector($componentClass, "{$componentClass}-is-custom");
}

$attrsOutput = '';

if ($selectTracking) {
	$attrsOutput .= " data-tracking='" . esc_attr($selectTracking) . "'";
}

$select = '
	<select
		class="' . esc_attr($selectClass) . '"
		name="' . esc_attr($selectName) . '"
		id="' . esc_attr($selectId) . '"
		' . disabled($selectIsDisabled, true, false) . '
		' . $attrsOutput . '
	>
		' . $selectOptions . '
	</select>
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
