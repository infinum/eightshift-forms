<?php

/**
 * Template for the Textarea Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

echo Helpers::render(
	'textarea',
	Helpers::props('textarea', $attributes)
);
