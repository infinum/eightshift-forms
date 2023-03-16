<?php

/**
 * Template for the Field Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$fieldName = $attributes['fieldFieldName'] ?? '';
$props = [];

if (empty($fieldName)) {
	$props['fieldName'] = Components::getUnique();
}

echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes, $props),
		[
			'selectorClass' => 'field'
		]
	)
);
