<?php

/**
 * Template for the Select Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\Blocks;
use EightshiftForms\Helpers\Components;

$unique = apply_filters(Blocks::BLOCKS_UNIQUE_STRING_FILTER_NAME, '');

$selectName = $attributes['selectSelectName'] ?? '';
$selectId = $attributes['selectSelectId'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$props = [];

if (empty($selectName)) {
	$props['selectName'] = $unique;
}

if (empty($selectId)) {
	$props['selectId'] = $unique;
}

$props['selectOptions'] = $innerBlockContent;
$props['blockClass'] = $blockClass;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'select',
	Components::props('select', $attributes, $props)
);
