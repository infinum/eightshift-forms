<?php

/**
 * Template for the Activecampaign Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

echo Helpers::render(
	'form',
	Helpers::props('form', $attributes, [
		'formContent' => $renderContent,
	])
);
