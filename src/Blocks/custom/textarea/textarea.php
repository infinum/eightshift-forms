<?php

/**
 * Template for the Textarea Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$unique = Components::getUnique();

$textareaName = $attributes['textareaTextareaName'] ?? '';
$textareaId = $attributes['textareaTextareaId'] ?? '';
$props = [];

if (empty($textareaName)) {
	$props['textareaName'] = $textareaId;
}

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'textarea',
	Components::props('textarea', $attributes, $props)
);
