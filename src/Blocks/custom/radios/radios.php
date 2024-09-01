<?php

/**
 * Template for the radios Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$props['radiosContent'] = $renderContent;
$props['twSelectorsData'] = FormsHelper::getTwSelectorsData($attributes);

echo Helpers::render(
	'radios',
	Helpers::props('radios', $attributes, $props)
);
