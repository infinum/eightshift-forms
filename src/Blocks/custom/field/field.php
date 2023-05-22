<?php

/**
 * Template for the Field Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

echo Components::render(
	'field',
	array_merge(
		Components::props('field', $attributes),
		[
			'selectorClass' => 'field'
		]
	)
);
