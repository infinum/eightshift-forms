<?php

/**
 * Template for the Select Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\Blocks;
use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$selectName = Components::checkAttr('selectName', $attributes, $manifest);
$selectIsDisabled = Components::checkAttr('selectIsDisabled', $attributes, $manifest);
$selectOptions = Components::checkAttr('selectOptions', $attributes, $manifest);
$selectIsRequired = Components::checkAttr('selectIsRequired', $attributes, $manifest);
$selectTracking = Components::checkAttr('selectTracking', $attributes, $manifest);

// Fix for getting attribute that is part of the child component.
$selectFieldLabel = $attributes[Components::getAttrKey('selectFieldLabel', $attributes, $manifest)] ?? '';

$selectClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
]);

$selectIsDisabled = $selectIsDisabled ? 'disabled' : '';

if (empty($selectName)) {
	$selectName = apply_filters(Blocks::BLOCKS_NAME_TO_ID_FILTER_NAME, $selectFieldLabel);
}

$selectId = apply_filters(Blocks::BLOCKS_NAME_TO_ID_FILTER_NAME, $selectName);

$select = '
	<select
		class="'. esc_attr($selectClass) .'"
		name="'. esc_attr($selectName) . '"
		id="'. esc_attr($selectId) . '"
		data-validation-required="' . $selectIsRequired . '"
		data-tracking="' . $selectTracking . '"
		' . $selectIsDisabled . '
	>
		' . $selectOptions . '
	</select>
';

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'field',
	Components::props('field', $attributes, [
		'fieldContent' => $select,
		'fieldId' => $selectId,
	])
);

?>


