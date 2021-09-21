<?php

/**
 * Template for the radio item Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\Blocks;
use EightshiftForms\Helpers\Components;

$unique = apply_filters(Blocks::BLOCKS_NAME_TO_ID_FILTER_NAME, '');

$radioId = $attributes['radioradioId'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$props = [];

if (empty($radioId)) {
	$props['radioId'] = $unique;
}

$props['blockClass'] = $blockClass;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'radio',
	Components::props('radio', $attributes, $props)
);
