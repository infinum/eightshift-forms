<?php

/**
 * Template for the Highlighted Content Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';

$highlightedContentTitle = Helpers::checkAttr('highlightedContentTitle', $attributes, $manifest);
$highlightedContentSubtitle = Helpers::checkAttr('highlightedContentSubtitle', $attributes, $manifest);
$highlightedContentIcon = Helpers::checkAttr('highlightedContentIcon', $attributes, $manifest);

$highlightedContentClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($additionalClass, $additionalClass),
]);

?>

<div class="<?php echo esc_attr($highlightedContentClass); ?>">
	<?php echo $highlightedContentIcon ? UtilsHelper::getUtilsIcons($highlightedContentIcon) : UtilsHelper::getUtilsIcons('warning'); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>

	<p class="<?php echo esc_attr("{$componentClass}__title"); ?>">
		<?php echo esc_html($highlightedContentTitle); ?>
	</p>

	<?php if ($highlightedContentSubtitle) { ?>
		<p class="<?php echo esc_attr("{$componentClass}__subtitle"); ?>">
			<?php echo wp_kses_post($highlightedContentSubtitle); ?>
		</p>
	<?php } ?>
</div>
