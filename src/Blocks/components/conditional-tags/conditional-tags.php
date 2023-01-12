<?php

/**
 * Template for the Conditional tags component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$conditionalTagsUse = Components::checkAttr('conditionalTagsUse', $attributes, $manifest);
$conditionalTagsAction = Components::checkAttr('conditionalTagsAction', $attributes, $manifest);
$conditionalTagsLogic = Components::checkAttr('conditionalTagsLogic', $attributes, $manifest);
$conditionalTagsRules = Components::checkAttr('conditionalTagsRules', $attributes, $manifest);

if (!$conditionalTagsUse) {
	return;
}

echo wp_json_encode([
	$conditionalTagsAction,
	$conditionalTagsLogic,
	$conditionalTagsRules,
]);
