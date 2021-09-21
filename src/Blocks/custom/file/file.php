<?php

/**
 * Template for the File Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\Blocks;
use EightshiftForms\Helpers\Components;

$unique = apply_filters(Blocks::BLOCKS_NAME_TO_ID_FILTER_NAME, '');

$fileName = $attributes['fileFileName'] ?? '';
$fileId = $attributes['fileFileId'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$props = [];

if (empty($fileName)) {
	$props['fileName'] = $unique;
}

if (empty($fileId)) {
	$props['fileId'] = $unique;
}

$props['blockClass'] = $blockClass;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'file',
	Components::props('file', $attributes, $props)
);
