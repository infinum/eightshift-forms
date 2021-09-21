<?php

/**
 * Template for the Textarea Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'textarea',
	Components::props('textarea', $attributes, [
		'blockClass' => $attributes['blockClass'] ?? '',
	])
);

