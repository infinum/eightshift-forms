<?php

/**
 * Template for the conditional-logic-repeater Component used in settings.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$conditionalLogicRepeaterFields = Components::checkAttr('conditionalLogicRepeaterFields', $attributes, $manifest);

$conditionalLogicOutput = '<conditional-logic-repeater fields="checkbox:Checkbox,name:Name,demo:Demo"></conditional-logic-repeater>';

echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes, [
			'fieldContent' => $conditionalLogicOutput,
			// 'fieldId' => $selectId,
			// 'fieldName' => $selectName,
			// 'fieldIsRequired' => $selectIsRequired,
			// 'fieldDisabled' => !empty($selectIsDisabled),
		]),
		[
			// 'additionalFieldClass' => $additionalFieldClass,
			// 'selectorClass' => $manifest['componentName'] ?? '',
		]
	)
);
