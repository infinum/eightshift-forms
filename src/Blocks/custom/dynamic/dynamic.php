<?php

/**
 * Template for the Dynamic Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

echo Helpers::render(
	'dynamic',
	Helpers::props('dynamic', $attributes)
);
