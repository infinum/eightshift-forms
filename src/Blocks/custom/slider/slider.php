<?php

/**
 * Template for the Slider Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

echo Components::render(
	'slider',
	Components::props('slider', $attributes)
);
