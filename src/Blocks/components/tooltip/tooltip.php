<?php

/**
 * Template for the Tooltip Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalTooltipClass = $attributes['additionalTooltipClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$tooltipContent = Components::checkAttr('tooltipContent', $attributes, $manifest);
$tooltipPosition = Components::checkAttr('tooltipPosition', $attributes, $manifest);

$tooltipClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($tooltipPosition, $componentClass, '', $tooltipPosition),
	Components::selector($selectorClass, $selectorClass, $componentClass),
	Components::selector($additionalTooltipClass, $additionalTooltipClass),
]);

?>

<span class="<?php echo esc_attr($tooltipClass); ?>">
	<svg class="<?php echo esc_attr("{$componentClass}__icon"); ?>" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><circle r="7.5" transform="matrix(1 0 0 -1 10 10)" stroke="currentColor" stroke-width="1.5"></circle><path d="M11 13.5c-1 .5-1.75 0-1.5-1L10 10c.15-.5-.5-1-1.5 0" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><circle r="1" transform="matrix(1 0 0 -1 10 6.75)" fill="currentColor"></circle></svg>
	<span class="<?php echo esc_attr("{$componentClass}__inner"); ?>">
		<?php echo esc_html($tooltipContent); ?>
	</span>
</span>
