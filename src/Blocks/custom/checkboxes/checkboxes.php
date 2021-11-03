<?php

/**
 * Template for the Checkboxes Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$checkboxesName = $attributes['checkboxesCheckboxesName'] ?? '';
$checkboxesId = $attributes['checkboxesCheckboxesId'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$props = [];

if (empty($checkboxesName)) {
	$props['checkboxesName'] = $checkboxesId;
}

$props['checkboxesContent'] = $innerBlockContent;
$props['blockClass'] = $blockClass;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'checkboxes',
	Components::props('checkboxes', $attributes, $props)
);
