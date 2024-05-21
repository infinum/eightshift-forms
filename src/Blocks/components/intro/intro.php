<?php

/**
 * Template for the Intro Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

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

$introClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($introIsHighlighted && $componentClass, $componentClass, 'highlighted'),
	Helpers::selector($introIsHighlightedImportant && $componentClass, $componentClass, 'highlighted', 'important'),
	Helpers::selector($introIsHeading && $componentClass, $componentClass, '', 'heading'),
	Helpers::selector($additionalClass, $additionalClass),
	Helpers::selector($introTitleSize, $componentClass, 'size', $introTitleSize),
	Helpers::selector($introIcon, $componentClass, '', 'with-icon'),
]);

$titleClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass, 'title'),
	Helpers::selector($introTitleSize, $componentClass, 'title', $introTitleSize),
]);

?>

<div class="<?php echo esc_attr($introClass); ?>">
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
		<div class="<?php echo esc_attr($titleClass); ?>">
			<?php echo esc_html($introTitle); ?>
		</div>
	<?php } ?>

	<?php if ($introSubtitle) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__subtitle"); ?>">
			<?php echo wp_kses_post($introSubtitle); ?>
		</div>
	<?php } ?>

	<?php if ($introHelp) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__help"); ?>">
			<?php echo wp_kses_post($introHelp); ?>
		</div>
	<?php } ?>
</div>
