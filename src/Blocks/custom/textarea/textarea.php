<?php

/**
 * Template for the Textarea Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$unique = Components::getUnique();

$textareaName = $attributes['textareaTextareaName'] ?? '';
$textareaId = $attributes['textareaTextareaId'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$props = [];

if (empty($textareaName)) {
	$props['textareaName'] = $unique;
}

if (empty($textareaId)) {
	$props['textareaId'] = $unique;
}

$props['blockClass'] = $blockClass;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'textarea',
	Components::props('textarea', $attributes, $props)
);
