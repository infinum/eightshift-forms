<?php

/**
 * Template for the checkbox item Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\Blocks;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$unique = Components::getUnique();

$checkboxLabel = $attributes['checkboxCheckboxLabel'] ?? '';
$checkboxName = $attributes['checkboxCheckboxName'] ?? '';
$checkboxValue = $attributes['checkboxCheckboxValue'] ?? '';
$props = [];

if (!$checkboxValue) {
	if ($checkboxLabel) {
		$props['checkboxValue'] = apply_filters(Blocks::BLOCKS_STRING_TO_VALUE_FILTER_NAME, $checkboxLabel);
	} else {
		$props['checkboxValue'] = 'true';
	}
}

echo Components::render(
	'checkbox',
	Components::props('checkbox', $attributes, $props)
);
