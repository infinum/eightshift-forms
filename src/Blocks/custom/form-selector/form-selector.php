<?php

/**
 * Template for the Form Selector Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Hooks\Filters;

// Add custom additional content filter.
if (has_filter(Filters::FILTER_BLOCK_FORM_SELECTOR_ADDITIONAL_CONTENT_NAME)) {
	echo apply_filters(Filters::FILTER_BLOCK_FORM_SELECTOR_ADDITIONAL_CONTENT_NAME, ''); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

echo $innerBlockContent; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
