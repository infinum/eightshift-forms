<?php

/**
 * Template for the checkbox item Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'checkbox',
	Components::props('checkbox', $attributes)
);
