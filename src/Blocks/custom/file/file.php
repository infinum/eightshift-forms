<?php

/**
 * Template for the File Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

echo Helpers::render(
	'file',
	Helpers::props('file', $attributes)
);
