<?php

/**
 * Template for the Input Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

echo Helpers::render(
	'input',
	Helpers::props('input', $attributes, [
		'twSelectorsData' => FormsHelper::getTwSelectorsData($attributes),
	])
);
