<?php

/**
 * Template for the Rating Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

echo Helpers::render(
	'rating',
	Helpers::props('rating', $attributes)
);
