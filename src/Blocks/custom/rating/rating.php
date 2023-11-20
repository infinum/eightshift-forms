<?php

/**
 * Template for the Rating Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

echo Components::render(
	'rating',
	Components::props('rating', $attributes)
);
