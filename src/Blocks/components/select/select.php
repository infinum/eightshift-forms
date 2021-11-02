<?php

/**
 * Template for the Select Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$selectId = Components::checkAttr('selectId', $attributes, $manifest);
$selectName = Components::checkAttr('selectName', $attributes, $manifest);
$selectIsDisabled = Components::checkAttr('selectIsDisabled', $attributes, $manifest);
$selectOptions = Components::checkAttr('selectOptions', $attributes, $manifest);
$selectTracking = Components::checkAttr('selectTracking', $attributes, $manifest);

// Fix for getting attribute that is part of the child component.
$selectFieldLabel = $attributes[Components::getAttrKey('selectFieldLabel', $attributes, $manifest)] ?? '';

$selectClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
]);

$selectIsDisabled = disabled($selectIsDisabled);

$select = '
	<select
		class="' . esc_attr($selectClass) . '"
		name="' . esc_attr($selectName) . '"
		id="' . esc_attr($selectId) . '"
		data-tracking="' . $selectTracking . '"
		' . $selectIsDisabled . '
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
			'additionalFieldClass' => $attributes['additionalFieldClass'] ?? '',
		]
	)
);
