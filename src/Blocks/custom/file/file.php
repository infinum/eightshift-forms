<?php

/**
 * Template for the File Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$unique = Components::getUnique();

$fileName = $attributes['fileFileName'] ?? '';
$fileId = $attributes['fileFileId'] ?? '';
$props = [];

if (empty($fileName)) {
	$props['fileName'] = $fileId;
}

echo Components::render(
	'file',
	Components::props('file', $attributes, $props)
);
