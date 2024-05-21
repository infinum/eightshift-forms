<?php

/**
 * Template for the Pipedrive Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

echo Helpers::render(
	'form',
	Helpers::props('form', $attributes, [
		'formContent' => $renderContent,
	])
);
