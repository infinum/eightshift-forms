<?php

/**
 * Template for the Input Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$inputName = $attributes['inputInputName'] ?? '';
$props = [];

if (empty($inputName)) {
	$props['inputName'] = Components::getUnique();
}

echo Components::render(
	'input',
	Components::props('input', $attributes, $props)
);
