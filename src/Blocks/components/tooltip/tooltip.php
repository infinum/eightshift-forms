<?php

/**
 * Template for the Tooltip Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);
$manifestUtils = Components::getComponent('utils');

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
	<?php echo $manifestUtils['icons']['tooltip']; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
	<span class="<?php echo esc_attr("{$componentClass}__inner"); ?>">
		<?php echo esc_html($tooltipContent); ?>
	</span>
</span>
