<?php

/**
 * Template for the Step Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\FormsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$stepName = $attributes['stepStepName'] ?? '';
$props = [
	'twSelectorsData' => FormsHelper::getTwSelectorsData($attributes),
];

if (empty($stepName)) {
	$props['stepName'] = Helpers::getUnique();
}

echo Helpers::render(
	'step',
	Helpers::props('step', $attributes, $props)
);
