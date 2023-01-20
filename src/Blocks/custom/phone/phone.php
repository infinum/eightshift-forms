<?php

/**
 * Template for the Phone Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$phoneName = $attributes['phonePhoneName'] ?? '';
$props = [];

if (empty($phoneName)) {
	$props['phoneName'] = Components::getUnique();
}

echo Components::render(
	'phone',
	Components::props('phone', $attributes, $props)
);
