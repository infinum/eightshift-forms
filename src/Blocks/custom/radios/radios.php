<?php

/**
 * Template for the radios Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$radiosName = $attributes['radiosRadiosName'] ?? '';
$props = [];

if (empty($radiosName)) {
	$props['radiosName'] = Components::getUnique();
}

$props['radiosContent'] = $innerBlockContent;

echo Components::render(
	'radios',
	Components::props('radios', $attributes, $props)
);
