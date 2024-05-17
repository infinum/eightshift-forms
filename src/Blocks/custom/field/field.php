<?php

/**
 * Template for the Field Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

echo Helpers::render(
	'field',
	array_merge(
		Helpers::props('field', $attributes),
		[
			'selectorClass' => 'field',
			'fieldFieldIsNoneFormBlock' => true,
			'fieldFieldConditionalTags' => Helpers::render(
				'conditional-tags',
				Helpers::props('conditionalTags', $attributes)
			),
		]
	)
);
