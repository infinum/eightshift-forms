<?php

/**
 * Template for the radios Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$props['radiosContent'] = $innerBlockContent;

echo Components::render(
	'radios',
	Components::props('radios', $attributes, $props)
);
