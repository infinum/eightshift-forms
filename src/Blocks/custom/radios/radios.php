<?php

/**
 * Template for the radios Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$props['radiosContent'] = $renderContent;

echo Helpers::render(
	'radios',
	Helpers::props('radios', $attributes, $props)
);
