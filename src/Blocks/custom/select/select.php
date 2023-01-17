<?php

/**
 * Template for the Select Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$unique = Components::getUnique();

$selectName = $attributes['selectSelectName'] ?? '';
$selectId = $attributes['selectSelectId'] ?? '';
$props = [];

if (empty($selectName)) {
	$props['selectName'] = $selectId;
}

$props['selectContent'] = $innerBlockContent;

echo Components::render(
	'select',
	Components::props('select', $attributes, $props)
);
