<?php

/**
 * Template for the Checkboxes Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$checkboxesName = $attributes['checkboxesCheckboxesName'] ?? '';
$props = [];

if (empty($checkboxesName)) {
	$props['checkboxesName'] = Components::getUnique();
}

$props['checkboxesContent'] = $innerBlockContent;

echo Components::render(
	'checkboxes',
	Components::props('checkboxes', $attributes, $props)
);
