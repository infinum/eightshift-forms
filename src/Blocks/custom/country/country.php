<?php

/**
 * Template for the Country Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$countryName = $attributes['countryCountryName'] ?? '';
$props = [];

if (empty($countryName)) {
	$props['countryName'] = Components::getUnique();
}

echo Components::render(
	'country',
	Components::props('country', $attributes, $props)
);
