<?php

/**
 * Template for the File Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

echo Components::render(
	'file',
	Components::props('file', $attributes)
);
