<?php

/**
 * Template for the Dynamic Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

echo Components::render(
	'dynamic',
	Components::props('dynamic', $attributes)
);
