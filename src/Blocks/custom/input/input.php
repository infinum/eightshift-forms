<?php

/**
 * Template for the Input Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

echo Components::render(
	'input',
	Components::props('input', $attributes)
);
