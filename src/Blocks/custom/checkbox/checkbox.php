<?php

/**
 * Template for the checkbox item Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

echo Helpers::render(
	'checkbox',
	Helpers::props('checkbox', $attributes)
);
