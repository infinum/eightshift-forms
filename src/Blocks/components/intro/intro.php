<?php

/**
 * Template for the Intro Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$introTitle = Helpers::checkAttr('introTitle', $attributes, $manifest);
$introTitleSize = Helpers::checkAttr('introTitleSize', $attributes, $manifest);
$introSubtitle = Helpers::checkAttr('introSubtitle', $attributes, $manifest);
$introHelp = Helpers::checkAttr('introHelp', $attributes, $manifest);
$introIsHighlighted = Helpers::checkAttr('introIsHighlighted', $attributes, $manifest);
$introIsHighlightedImportant = Helpers::checkAttr('introIsHighlightedImportant', $attributes, $manifest);
$introIsHeading = Helpers::checkAttr('introIsHeading', $attributes, $manifest);
$introIcon = Helpers::checkAttr('introIcon', $attributes, $manifest);

?>

<div class="esf:flex esf:flex-col esf:gap-5">
	<?php
	if ($introIsHighlightedImportant) {
		// phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
		echo UtilsHelper::getUtilsIcons('warning');
	}

	if ($introIcon) {
		// phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
		echo UtilsHelper::getUtilsIcons($introIcon);
	}
	?>

	<?php if ($introTitle) { ?>
		<div class="esf:text-xl esf:font-medium">
			<?php echo esc_html($introTitle); ?>
		</div>
	<?php } ?>

	<?php if ($introSubtitle) { ?>
		<div class="esf:text-sm esf:text-secondary-500">
			<?php echo wp_kses_post($introSubtitle); ?>
		</div>
	<?php } ?>

	<?php if ($introHelp) { ?>
		<div class="esf:text-secondary-400 esf:text-xs">
			<?php echo wp_kses_post($introHelp); ?>
		</div>
	<?php } ?>
</div>
