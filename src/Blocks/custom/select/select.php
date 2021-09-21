<?php

/**
 * Template for the Select Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'select',
	Components::props('select', $attributes, [
		'selectOptions' => $innerBlockContent,
		'blockClass' => $attributes['blockClass'] ?? '',
	])
);
