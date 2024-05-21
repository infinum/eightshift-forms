<?php

/**
 * Template for the Checkboxes Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$props['checkboxesContent'] = $renderContent;

echo Helpers::render(
	'checkboxes',
	Helpers::props('checkboxes', $attributes, $props)
);
