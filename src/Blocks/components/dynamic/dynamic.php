<?php

/**
 * Template for the Dynamic Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\HooksHelpers;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$dynamicName = Helpers::checkAttr('dynamicName', $attributes, $manifest);
if (!$dynamicName) {
	return;
}

$dynamicIsDeactivated = Helpers::checkAttr('dynamicIsDeactivated', $attributes, $manifest);

if ($dynamicIsDeactivated) {
	return;
}

$filterName = HooksHelpers::getFilterName(['block', 'dynamic', 'dataOutput']);

echo apply_filters( // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
	$filterName,
	$attributes,
	Helpers::checkAttr('dynamicFormPostId', $attributes, $manifest)
);
