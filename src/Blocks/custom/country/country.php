<?php

/**
 * Template for the Country Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

echo Helpers::render(
	'country',
	Helpers::props('country', $attributes, [
		'twSelectorsData' => FormsHelper::getTwSelectorsData($attributes),
	])
);
