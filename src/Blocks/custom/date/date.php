<?php

/**
 * Template for the Date Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

echo Helpers::render(
	'date',
	Helpers::props('date', $attributes)
);
