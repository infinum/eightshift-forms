<?php

/**
 * Template for the Moments Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

dump($renderContent);

echo Helpers::render(
	'form',
	Helpers::props('form', $attributes, [
		'formContent' => $renderContent,
	])
);
