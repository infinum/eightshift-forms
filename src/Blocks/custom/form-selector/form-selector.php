<?php

/**
 * Template for the Form Selector Block view.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\GeneralHelpers;

// Add custom additional content filter.
echo GeneralHelpers::getBlockAdditionalContentViaFilter('formSelector', $attributes); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped

echo $renderContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
