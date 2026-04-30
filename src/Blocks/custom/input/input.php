<?php

/**
 * Template for the Input Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

echo Helpers::render(
	'input',
	Helpers::props('input', $attributes)
);
