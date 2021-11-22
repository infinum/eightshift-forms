<?php

/**
 * Template for the File Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$unique = Components::getUnique();

$fileName = $attributes['fileFileName'] ?? '';
$fileId = $attributes['fileFileId'] ?? '';
$props = [];

if (empty($fileName)) {
	$props['fileName'] = $fileId;
}

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'file',
	Components::props('file', $attributes, $props)
);
