<?php

/**
 * Template for the conditional-logic-repeater Component used in settings.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$componentJsClass = $manifest['componentJsClass'] ?? '';
$componentIdPrefix = $manifest['componentIdPrefix'] ?? '';

$conditionalLogicRepeaterFields = Components::checkAttr('conditionalLogicRepeaterFields', $attributes, $manifest);
$conditionalLogicRepeaterValue = Components::checkAttr('conditionalLogicRepeaterValue', $attributes, $manifest);
$conditionalLogicRepeaterName = Components::checkAttr('conditionalLogicRepeaterName', $attributes, $manifest);
$conditionalLogicRepeaterId = Components::checkAttr('conditionalLogicRepeaterId', $attributes, $manifest);
$conditionalLogicRepeaterUse = Components::checkAttr('conditionalLogicRepeaterUse', $attributes, $manifest);

$fields = wp_json_encode($conditionalLogicRepeaterFields);

$conditionalLogicRepeaterClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($componentJsClass, $componentJsClass),
]);

$id = "{$componentIdPrefix}---{$conditionalLogicRepeaterId}";

$output = "
	<conditional-logic-repeater
		fields='$fields'
		toggleable='$conditionalLogicRepeaterUse'
		class='$conditionalLogicRepeaterClass'
		data-id='$id'
	></conditional-logic-repeater>";

$output .= Components::render(
	'input',
	array_merge(
		Components::props('input', $attributes, [
			'inputType' => 'hidden',
			'inputId' => $id,
			'inputName' => $id,
			'inputValue' => $conditionalLogicRepeaterValue,
		]),
	)
);

echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $output,
			'fieldId' => $id,
			'fieldName' => $id,
		]),
	)
);
