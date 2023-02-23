<?php

/**
 * Template for the Submit Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$submitName = $attributes['submitSubmitName'] ?? '';
$props = [];

if (empty($submitName)) {
	$props['submitName'] = Components::getUnique();
}

echo Components::render(
	'submit',
	Components::props('submit', $attributes, $props)
);
