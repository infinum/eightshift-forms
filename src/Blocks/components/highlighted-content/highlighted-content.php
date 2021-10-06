<?php

/**
 * Template for the Highlighted Content Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$highlightedContentTitle = Components::checkAttr('highlightedContentTitle', $attributes, $manifest);
$highlightedContentSubtitle = Components::checkAttr('highlightedContentSubtitle', $attributes, $manifest);

$highlightedContentClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
]);

?>

<div class="<?php echo esc_attr($highlightedContentClass); ?>">
	<div class="<?php echo esc_attr("{$componentClass}__title"); ?>">
		<?php echo esc_html($highlightedContentTitle); ?>
	</div>

	<?php if ($highlightedContentSubtitle) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__subtitle"); ?>">
			<?php echo $highlightedContentSubtitle; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php } ?>
</div>
