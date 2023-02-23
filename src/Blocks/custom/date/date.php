<?php

/**
 * Template for the Date Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$dateName = $attributes['dateDateName'] ?? '';
$props = [];

if (empty($dateName)) {
	$props['dateName'] = Components::getUnique();
}

echo Components::render(
	'date',
	Components::props('date', $attributes, $props)
);
