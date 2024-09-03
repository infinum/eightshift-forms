<?php

/**
 * Template for the radio item Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

echo Helpers::render(
	'radio',
	Helpers::props('radio', $attributes, [
		'twSelectorsData' => FormsHelper::getTwSelectorsData($attributes),
	])
);
