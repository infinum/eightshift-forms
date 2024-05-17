<?php

/**
 * Template for the Form Selector Block view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsGeneralHelper;

// Add custom additional content filter.
echo UtilsGeneralHelper::getBlockAdditionalContentViaFilter('formSelector', $attributes); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped

echo $renderContent; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
