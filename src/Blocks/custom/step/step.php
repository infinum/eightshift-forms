<?php

/**
 * Template for the Step Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$stepName = $attributes['stepStepName'] ?? '';
$props = [];

if (empty($stepName)) {
	$props['stepName'] = Helpers::getUnique();
}

echo Helpers::render(
	'step',
	Helpers::props('step', $attributes, $props)
);
