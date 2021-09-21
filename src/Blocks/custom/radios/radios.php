<?php

/**
 * Template for the radios Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'radios',
	Components::props('radios', $attributes, [
		'radiosContent' => $innerBlockContent,
		'blockClass' => $attributes['blockClass'] ?? '',
	])
);
