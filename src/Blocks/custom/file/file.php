<?php

/**
 * Template for the file Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'file',
	Components::props('file', $attributes, [
		'blockClass' => $attributes['blockClass'] ?? '',
	])
);
