<?php

/**
 * Template for the Select Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$selectName = $attributes['selectSelectName'] ?? '';
$props = [];

if (empty($selectName)) {
	$props['selectName'] = Components::getUnique();
}

$props['selectContent'] = $innerBlockContent;

echo Components::render(
	'select',
	Components::props('select', $attributes, $props)
);
