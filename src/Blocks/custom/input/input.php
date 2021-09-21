<?php

/**
 * Template for the Input Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'input',
	Components::props('input', $attributes, [
		'blockClass' => $attributes['blockClass'] ?? '',
	])
);
?>
