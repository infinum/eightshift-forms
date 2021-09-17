<?php

/**
 * Template for the Forms Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$blockClass = $attributes['blockClass'] ?? '';

$formsForm = Components::checkAttr('formsForm', $attributes, $manifest);

echo \apply_filters('the_content', \get_post_field('post_content', $formsForm)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
