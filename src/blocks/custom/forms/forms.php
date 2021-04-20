<?php

/**
 * Template for the Forms Block view.
 *
 * @package EightshiftForms\Blocks.
 */

namespace EightshiftForms\Blocks;

use EightshiftForms\View\FormView;
use EightshiftForms\Helpers\Forms;

$blockClass      = $attributes['blockClass'] ?? '';
$selectedFormId = $attributes['selectedFormId'] ?? 0;
$theme            = $attributes['theme'] ?? '';

$postContent = get_post_field('post_content', $selectedFormId);

if (! empty($theme)) {
	$postBlocks = Forms::recursively_change_theme_for_all_blocks(parse_blocks($postContent), $theme);
} else {
	$postBlocks = parse_blocks($postContent);
}

foreach ($postBlocks as $postBlock) {
	echo wp_kses(render_block($postBlock), FormView::allowed_tags());
}
