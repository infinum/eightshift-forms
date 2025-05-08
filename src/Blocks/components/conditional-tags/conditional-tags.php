<?php

/**
 * Template for the Conditional tags component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$conditionalTagsUse = Helpers::checkAttr('conditionalTagsUse', $attributes, $manifest);
$conditionalTagsRules = Helpers::checkAttr('conditionalTagsRules', $attributes, $manifest);

if (!$conditionalTagsUse) {
	return;
}

echo wp_json_encode([
	$conditionalTagsRules,
]);
