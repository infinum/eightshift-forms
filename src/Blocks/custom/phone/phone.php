<?php

/**
 * Template for the Phone Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

echo Helpers::render(
	'phone',
	Helpers::props('phone', $attributes, [
		'twSelectorsData' => FormsHelper::getTwSelectorsData($attributes),
	])
);
