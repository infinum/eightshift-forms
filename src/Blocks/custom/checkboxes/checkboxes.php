<?php

/**
 * Template for the Checkboxes Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$props['checkboxesContent'] = $innerBlockContent;

echo Components::render(
	'checkboxes',
	Components::props('checkboxes', $attributes, $props)
);
