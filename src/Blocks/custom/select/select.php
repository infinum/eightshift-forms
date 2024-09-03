<?php

/**
 * Template for the Select Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$props['selectContent'] = $renderContent;
$props['twSelectorsData'] = FormsHelper::getTwSelectorsData($attributes);

echo Helpers::render(
	'select',
	Helpers::props('select', $attributes, $props)
);
