<?php

/**
 * Template for the Select Option Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\Blocks;
use EightshiftForms\Helpers\Components;

$unique = apply_filters(Blocks::BLOCKS_UNIQUE_STRING_FILTER_NAME, '');

$selectOptionLabel = $attributes['selectOptionSelectOptionLabel'] ?? '';
$selectOptionValue = $attributes['selectOptionSelectOptionValue'] ?? '';
$props = [];

if (empty($selectOptionValue)) {
	$props['selectOptionValue'] = apply_filters(Blocks::BLOCKS_STRING_TO_VALUE_FILTER_NAME, $selectOptionLabel);
}

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'select-option',
	Components::props('selectOption', $attributes, $props)
);
