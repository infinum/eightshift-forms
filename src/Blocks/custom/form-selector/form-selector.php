<?php

/**
 * Template for the Form Selector Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Hooks\Filters;

// Add custom additional content filter.
$filterName = Filters::getBlockFilterName('formSelector', 'additionalContent');
if (has_filter($filterName)) {
	echo apply_filters($filterName, ''); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

echo $innerBlockContent; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
