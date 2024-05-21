<?php

/**
 * Template for the Select Option Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

echo Helpers::render(
	'select-option',
	Helpers::props('selectOption', $attributes)
);
