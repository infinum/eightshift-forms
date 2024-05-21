<?php

/**
 * Template for the Greenhouse Block view.
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
