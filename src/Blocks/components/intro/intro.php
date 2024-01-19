<?php

/**
 * Template for the Intro Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$introTitle = Components::checkAttr('introTitle', $attributes, $manifest);
$introTitleSize = Components::checkAttr('introTitleSize', $attributes, $manifest);
$introSubtitle = Components::checkAttr('introSubtitle', $attributes, $manifest);
$introHelp = Components::checkAttr('introHelp', $attributes, $manifest);
$introIsHighlighted = Components::checkAttr('introIsHighlighted', $attributes, $manifest);
$introIsHighlightedImportant = Components::checkAttr('introIsHighlightedImportant', $attributes, $manifest);
$introIsHeading = Components::checkAttr('introIsHeading', $attributes, $manifest);
$introIcon = Components::checkAttr('introIcon', $attributes, $manifest);

$introClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($introIsHighlighted && $componentClass, $componentClass, 'highlighted'),
	Components::selector($introIsHighlightedImportant && $componentClass, $componentClass, 'highlighted', 'important'),
	Components::selector($introIsHeading && $componentClass, $componentClass, '', 'heading'),
	Components::selector($additionalClass, $additionalClass),
	Components::selector($introTitleSize, $componentClass, 'size', $introTitleSize),
	Components::selector($introIcon, $componentClass, '', 'with-icon'),
]);

$titleClass = Components::classnames([
	Components::selector($componentClass, $componentClass, 'title'),
	Components::selector($introTitleSize, $componentClass, 'title', $introTitleSize),
]);

?>

<div class="<?php echo esc_attr($introClass); ?>">
	<?php
	if ($introIsHighlightedImportant) {
		// phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
		echo UtilsHelper::getUtilsIcons('warning');
	}

	if ($introIcon) {
		// phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped
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
