<?php

/**
 * Template for the Submit Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$unique = Components::getUnique();

$submitName = $attributes['submitSubmitName'] ?? '';
$submitId = $attributes['submitSubmitId'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$props = [];

$props['blockClass'] = $blockClass;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'submit',
	Components::props('submit', $attributes, $props)
);
