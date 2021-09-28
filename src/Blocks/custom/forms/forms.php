<?php

/**
 * Template for the Forms Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;

$manifest = Components::getManifest(__DIR__);
$globalManifest = Components::getManifest(dirname(__DIR__, 2));

echo Components::outputCssVariablesGlobal($globalManifest); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

$blockClass = $attributes['blockClass'] ?? '';

// Check formPost ID prop.
$formsFormPostId = Components::checkAttr('formsFormPostId', $attributes, $manifest);

// Bailout if form post ID is missing.
if (!$formsFormPostId) {
	return;
}

// Convert blocks to array.
$blocks = parse_blocks(get_the_content(null, null, $formsFormPostId));

// Bailout if it fails for some reason.
if (!$blocks) {
	return;
}

// Encrypt.
$formsFormPostId = (string) Helper::encryptor('encrypt', $formsFormPostId);

// Iterate blocks an children by passing them form ID.
foreach ($blocks as $key => $block) {
	if ($block['blockName'] === $globalManifest['namespace'] . '/form-selector') {
		$blocks[$key]['attrs']['formSelectorFormPostId'] = $formsFormPostId;

		foreach ($block['innerBlocks'] as $innerKey => $innerBlock) {
			$name = Components::kebabToCamelCase(str_replace($globalManifest['namespace'] . '/', '', $innerBlock['blockName']));
			$blocks[$key]['innerBlocks'][$innerKey]['attrs']["formPostId"] = $formsFormPostId;
		}
	}
}

// Render blocks.
foreach ($blocks as $block) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo \apply_filters('the_content', \render_block($block));
}
