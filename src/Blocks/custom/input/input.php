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
$props = [];

if (empty($inputName)) {
	$props['inputName'] = $inputId;
}

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'input',
	Components::props('input', $attributes, $props)
);
