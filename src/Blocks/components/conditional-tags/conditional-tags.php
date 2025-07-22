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

echo htmlspecialchars(wp_json_encode( // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
	$conditionalTagsRules,
), ENT_QUOTES | ENT_HTML5);
