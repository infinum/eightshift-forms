<?php

/**
 * Template for the File Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$fileName = $attributes['fileFileName'] ?? '';
$props = [];

if (empty($fileName)) {
	$props['fileName'] = Components::getUnique();
}

echo Components::render(
	'file',
	Components::props('file', $attributes, $props)
);
