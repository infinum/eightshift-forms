<?php

/**
 * Template for the Checkboxes Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'checkboxes',
	Components::props('checkboxes', $attributes, [
		'checkboxesContent' => $innerBlockContent,
		'blockClass' => $attributes['blockClass'] ?? '',
	])
);
