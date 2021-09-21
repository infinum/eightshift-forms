<?php

/**
 * Template for the radios Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\Blocks;
use EightshiftForms\Helpers\Components;

$unique = apply_filters(Blocks::BLOCKS_NAME_TO_ID_FILTER_NAME, '');

$radiosName = $attributes['radiosradiosName'] ?? '';
$radiosId = $attributes['radiosradiosId'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$props = [];

if (empty($radiosName)) {
	$props['radiosName'] = $unique;
}

$props['radiosContent'] = $innerBlockContent;
$props['blockClass'] = $blockClass;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'radios',
	Components::props('radios', $attributes, $props)
);
