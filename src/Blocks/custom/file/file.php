<?php

/**
 * Template for the File Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

echo Helpers::render(
	'file',
	Helpers::props('file', $attributes, [
		'twSelectorsData' => FormsHelper::getTwSelectorsData($attributes),
	])
);
