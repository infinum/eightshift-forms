<?php

/**
 * Template for the Checkboxes Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$props['checkboxesContent'] = $renderContent;
$props['twSelectorsData'] = FormsHelper::getTwSelectorsData($attributes);

echo Helpers::render(
	'checkboxes',
	Helpers::props('checkboxes', $attributes, $props)
);
