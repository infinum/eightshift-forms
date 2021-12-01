<?php

/**
 * Template for the Form Selector Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Hooks\Filters;

// Add custom text before content filter.
$beforeContent = apply_filters(Filters::FILTER_BLOCK_FORM_SELECTOR_BEFORE_CONTENT_NAME, '');
if (
	has_filter(Filters::FILTER_BLOCK_FORM_SELECTOR_BEFORE_CONTENT_NAME) &&
	!empty($beforeContent)
) {
	echo $beforeContent; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

echo $innerBlockContent; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
