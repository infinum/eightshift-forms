<?php

/**
 * Template for the Submit Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\Blocks;
use EightshiftForms\Helpers\Components;

$unique = apply_filters(Blocks::BLOCKS_NAME_TO_ID_FILTER_NAME, '');

$submitName = $attributes['submitSubmitName'] ?? '';
$submitId = $attributes['submitSubmitId'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$props = [];

if (empty($submitId)) {
	$props['submitId'] = $unique;
}

$props['blockClass'] = $blockClass;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'submit',
	Components::props('submit', $attributes, $props)
);
