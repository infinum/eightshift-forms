<?php

/**
 * Template for the Form Selector Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\Helper;

// Add custom additional content filter.
echo Helper::getBlockAdditionalContentViaFilter('formSelector', $attributes); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped

echo $innerBlockContent; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
