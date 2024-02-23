<?php

/**
 * Template for the Dynamic Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$dynamicName = Components::checkAttr('dynamicName', $attributes, $manifest);
if (!$dynamicName) {
	return;
}

$dynamicIsDeactivated = Components::checkAttr('dynamicIsDeactivated', $attributes, $manifest);

if ($dynamicIsDeactivated) {
	return;
}

$filterName = UtilsHooksHelper::getFilterName(['block', 'dynamic', 'dataOutput']);

echo apply_filters( // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
	$filterName,
	$attributes,
	Components::checkAttr('dynamicFormPostId', $attributes, $manifest)
);
