<?php

/**
 * Template for the Dynamic Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHooksHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$dynamicName = Helpers::checkAttr('dynamicName', $attributes, $manifest);
if (!$dynamicName) {
	return;
}

$dynamicIsDeactivated = Helpers::checkAttr('dynamicIsDeactivated', $attributes, $manifest);

if ($dynamicIsDeactivated) {
	return;
}

$filterName = UtilsHooksHelper::getFilterName(['block', 'dynamic', 'dataOutput']);

echo apply_filters( // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
	$filterName,
	$attributes,
	Helpers::checkAttr('dynamicFormPostId', $attributes, $manifest)
);
