<?php

/**
 * Template for the radio item Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	'radio',
	Components::props('radio', $attributes)
);
