<?php

/**
 * Template for the Highlighted Content Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$highlightedContentTitle = Components::checkAttr('highlightedContentTitle', $attributes, $manifest);
$highlightedContentSubtitle = Components::checkAttr('highlightedContentSubtitle', $attributes, $manifest);
$highlightedContentIcon = Components::checkAttr('highlightedContentIcon', $attributes, $manifest);

$highlightedContentClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalClass, $additionalClass),
]);

?>

<div class="<?php echo esc_attr($highlightedContentClass); ?>">
	<?php echo $highlightedContentIcon ? UtilsHelper::getUtilsIcons($highlightedContentIcon) : UtilsHelper::getUtilsIcons('warning'); // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>

	<p class="<?php echo esc_attr("{$componentClass}__title"); ?>">
		<?php echo esc_html($highlightedContentTitle); ?>
	</p>

	<?php if ($highlightedContentSubtitle) { ?>
		<p class="<?php echo esc_attr("{$componentClass}__subtitle"); ?>">
			<?php echo wp_kses_post($highlightedContentSubtitle); ?>
		</p>
	<?php } ?>
</div>
