<?php

/**
 * Template for the radio item Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\Blocks;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$unique = Components::getUnique();

$radioLabel = $attributes['radioRadioLabel'] ?? '';
$radioId = $attributes['radioRadioId'] ?? '';
$radioValue = $attributes['radioRadioValue'] ?? '';
$props = [];

if (!$radioValue) {
	if ($radioLabel) {
		$props['radioValue'] = apply_filters(Blocks::BLOCKS_STRING_TO_VALUE_FILTER_NAME, $radioLabel);
	} else {
		$props['radioValue'] = 'true';
	}
}

echo Components::render(
	'radio',
	Components::props('radio', $attributes, $props)
);
