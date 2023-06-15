<?php

/**
 * Template for the Step Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$stepName = $attributes['stepStepName'] ?? '';
$props = [];

if (empty($stepName)) {
	$props['stepName'] = Components::getUnique();
}

echo Components::render(
	'step',
	Components::props('step', $attributes, $props)
);
