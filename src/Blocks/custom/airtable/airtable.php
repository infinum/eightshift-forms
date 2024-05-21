<?php

/**
 * Template for the Airtable Block view.
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
