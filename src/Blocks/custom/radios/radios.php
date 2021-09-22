<?php

/**
 * Template for the radios Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\Blocks;
use EightshiftForms\Helpers\Components;

$unique = apply_filters(Blocks::BLOCKS_UNIQUE_STRING_FILTER_NAME, '');

$radiosName = $attributes['radiosRadiosName'] ?? '';
$radiosId = $attributes['radiosRadiosId'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$props = [];

if (empty($radiosName)) {
	$props['radiosName'] = $unique;
}

if (empty($radiosName)) {
	$props['radiosName'] = $unique;
}

$props['radiosContent'] = $innerBlockContent;
$props['blockClass'] = $blockClass;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'radios',
	Components::props('radios', $attributes, $props)
);
