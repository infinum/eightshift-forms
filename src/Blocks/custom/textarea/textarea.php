<?php

/**
 * Template for the Textarea Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

echo Components::render(
	'textarea',
	Components::props('textarea', $attributes)
);
