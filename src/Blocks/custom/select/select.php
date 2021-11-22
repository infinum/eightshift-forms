<?php

/**
 * Template for the Select Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$unique = Components::getUnique();

$selectName = $attributes['selectSelectName'] ?? '';
$selectId = $attributes['selectSelectId'] ?? '';
$props = [];

if (empty($selectName)) {
	$props['selectName'] = $selectId;
}

$props['selectOptions'] = $innerBlockContent;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'select',
	Components::props('select', $attributes, $props)
);
