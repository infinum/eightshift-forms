<?php

/**
 * Template for the Form Selector Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Hooks\Filters;

// Add custom additional content filter.
$filterName = Filters::getFilterName(['block', 'formSelector', 'additionalContent']);
if (has_filter($filterName)) {
	echo apply_filters($filterName, ''); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
}

echo $innerBlockContent; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
