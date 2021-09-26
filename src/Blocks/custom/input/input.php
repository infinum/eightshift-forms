<?php

/**
 * Template for the Input Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$unique = Components::getUnique();

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
