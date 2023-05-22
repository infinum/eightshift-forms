<?php

/**
 * Template for the Country Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

echo Components::render(
	'country',
	Components::props('country', $attributes)
);
