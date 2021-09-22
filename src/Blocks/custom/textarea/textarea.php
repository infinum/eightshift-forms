<?php

/**
 * Template for the Textarea Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\Blocks;
use EightshiftForms\Helpers\Components;

$unique = apply_filters(Blocks::BLOCKS_UNIQUE_STRING_FILTER_NAME, '');

$textareaName = $attributes['textareaTextareaName'] ?? '';
$textareaId = $attributes['textareaTextareaId'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$props = [];

if (empty($textareaName)) {
	$props['textareaName'] = $unique;
}

if (empty($textareaId)) {
	$props['textareaId'] = $unique;
}

$props['blockClass'] = $blockClass;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'textarea',
	Components::props('textarea', $attributes, $props)
);


