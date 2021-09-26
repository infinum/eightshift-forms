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

$formsForm = Components::checkAttr('formsForm', $attributes, $manifest);

$blocks = parse_blocks(get_the_content(null, null, $formsForm));

// Encrypt form post ID for security reasons.
$blocks[0]['attrs']['formFormPostId'] = (string) Helper::encryptor('encrypt', $formsForm);

foreach ($blocks as $block) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo \apply_filters('the_content', \render_block($block));
}
