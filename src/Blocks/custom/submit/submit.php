<?php

/**
 * Template for the Submit Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$unique = Components::getUnique();

$submitName = $attributes['submitSubmitName'] ?? '';
$submitId = $attributes['submitSubmitId'] ?? '';
$props = [];

if (empty($submitName)) {
	$props['submitName'] = $submitId;
}

echo Components::render(
	'submit',
	Components::props('submit', $attributes, $props)
);
