<?php

/**
 * Template for the Select Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$props['selectContent'] = $renderContent;

echo Helpers::render(
	'select',
	Helpers::props('select', $attributes, $props)
);
