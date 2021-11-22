<?php

/**
 * Template for the radios Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$unique = Components::getUnique();

$radiosName = $attributes['radiosRadiosName'] ?? '';
$radiosId = $attributes['radiosRadiosId'] ?? '';
$props = [];

if (empty($radiosName)) {
	$props['radiosName'] = $radiosId;
}

$props['radiosContent'] = $innerBlockContent;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'radios',
	Components::props('radios', $attributes, $props)
);
