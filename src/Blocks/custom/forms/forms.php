<?php

/**
 * Template for the Forms Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;
use EightshiftForms\Helpers\Helper;

$manifest = Components::getManifest(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$formsForm = Components::checkAttr('formsForm', $attributes, $manifest);

$blocks = parse_blocks(get_the_content(null, null, $formsForm));

$blocks[0]['attrs']['formFormPostId'] = (string) Helper::encryptor('encrypt', $formsForm);

foreach ($blocks as $block) {
	echo \apply_filters('the_content', \render_block($block)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
