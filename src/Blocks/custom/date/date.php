<?php

/**
 * Template for the Date Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

echo Components::render(
	'date',
	Components::props('date', $attributes)
);
