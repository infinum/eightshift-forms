<?php

/**
 * Template for the Submit Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'submit',
	Components::props('submit', $attributes, [
		'blockClass' => $attributes['blockClass'] ?? '',
	])
);
