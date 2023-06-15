<?php

/**
 * Template for the Select Option Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

echo Components::render(
	'select-option',
	Components::props('selectOption', $attributes)
);
