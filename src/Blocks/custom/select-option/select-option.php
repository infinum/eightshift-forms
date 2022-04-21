<?php

/**
 * Template for the Select Option Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\Blocks;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$unique = Components::getUnique();

$selectOptionLabel = $attributes['selectOptionSelectOptionLabel'] ?? '';
$selectOptionValue = $attributes['selectOptionSelectOptionValue'] ?? '';
$props = [];

if (empty($selectOptionValue)) {
	$props['selectOptionValue'] = apply_filters(Blocks::BLOCKS_STRING_TO_VALUE_FILTER_NAME, $selectOptionLabel);
}

echo Components::render(
	'select-option',
	Components::props('selectOption', $attributes, $props)
);
