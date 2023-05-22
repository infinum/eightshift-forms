<?php

/**
 * Template for the Select Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$props['selectContent'] = $innerBlockContent;

echo Components::render(
	'select',
	Components::props('select', $attributes, $props)
);
