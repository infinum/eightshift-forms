<?php

/**
 * Template for the checkbox item Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Blocks\Blocks;
use EightshiftForms\Helpers\Components;

$unique = Components::getUnique();

$checkboxLabel = $attributes['checkboxCheckboxLabel'] ?? '';
$checkboxName = $attributes['checkboxCheckboxName'] ?? '';
$checkboxId = $attributes['checkboxCheckboxId'] ?? '';
$checkboxValue = $attributes['checkboxCheckboxValue'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$props = [];

if (empty($checkboxName)) {
	$props['checkboxName'] = $checkboxId;
}

if (empty($checkboxValue)) {
	$props['checkboxValue'] = apply_filters(Blocks::BLOCKS_STRING_TO_VALUE_FILTER_NAME, $checkboxLabel);
}

$props['blockClass'] = $blockClass;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'checkbox',
	Components::props('checkbox', $attributes, $props)
);
