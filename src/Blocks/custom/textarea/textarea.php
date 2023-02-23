<?php

/**
 * Template for the Textarea Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$textareaName = $attributes['textareaTextareaName'] ?? '';
$props = [];

if (empty($textareaName)) {
	$props['textareaName'] = Components::getUnique();
}

echo Components::render(
	'textarea',
	Components::props('textarea', $attributes, $props)
);
