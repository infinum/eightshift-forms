<?php

/**
 * Template for the Phone Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

echo Helpers::render(
	'phone',
	Helpers::props('phone', $attributes)
);
