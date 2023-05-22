<?php

/**
 * Template for the Phone Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

echo Components::render(
	'phone',
	Components::props('phone', $attributes)
);
