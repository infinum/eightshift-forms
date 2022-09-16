<?php

/**
 * Template for the sorting Component used in settings.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$componentTriggerJsClass = $manifest['componentTriggerJsClass'] ?? '';
$componentUpdateJsClass = $manifest['componentUpdateJsClass'] ?? '';

$triggerClass = Components::classnames([
	Components::selector($componentClass, $componentClass, 'trigger'),
	Components::selector($componentTriggerJsClass, $componentTriggerJsClass),
]);

$updateClass = Components::classnames([
	Components::selector($componentClass, $componentClass, 'update'),
	Components::selector($componentUpdateJsClass, $componentUpdateJsClass),
]);

echo Components::render(
	'submit',
	Components::props('trigger', $attributes, [
		'triggerValue' => __('Change order', 'eightshift-forms'),
		'triggerIsLayoutFree' => true,
		'triggerIcon' => 'order',
		'additionalClass' => $triggerClass,
	]),
	'',
	true
);

echo Components::render(
	'submit',
	Components::props('update', $attributes, [
		'updateValue' => __('Update order', 'eightshift-forms'),
		'updateIsLayoutFree' => true,
		'updateIcon' => 'save',
		'additionalClass' => $updateClass,
	]),
	'',
	true
);
