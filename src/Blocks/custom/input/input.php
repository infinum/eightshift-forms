<?php

/**
 * Template for the Input Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\Blocks;
use EightshiftForms\Helpers\Components;

$unique = apply_filters(Blocks::BLOCKS_NAME_TO_ID_FILTER_NAME, '');

$inputName = $attributes['inputInputName'] ?? '';
$inputId = $attributes['inputInputId'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$props = [];

if (empty($inputName)) {
	$props['inputName'] = $unique;
}

if (empty($inputId)) {
	$props['inputId'] = $unique;
}

$props['blockClass'] = $blockClass;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'input',
	Components::props('input', $attributes, $props)
);
