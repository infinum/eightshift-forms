<?php

/**
 * Template for the Tooltip Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$componentClass = $manifest['componentClass'] ?? '';
$additionalTooltipClass = $attributes['additionalTooltipClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$tooltipContent = Helpers::checkAttr('tooltipContent', $attributes, $manifest);
$tooltipPosition = Helpers::checkAttr('tooltipPosition', $attributes, $manifest);

$tooltipInnerPositionClass = match ($tooltipPosition) {
	'top' => 'esf:bottom-full esf:left-1/2 esf:-translate-x-1/2 esf:-translate-y-[10px]',
	'right' => 'esf:top-1/2 esf:left-full esf:translate-x-[10px] esf:-translate-y-1/2',
	'bottom' => 'esf:top-full esf:left-1/2 esf:-translate-x-1/2 esf:translate-y-[10px]',
	'left' => 'esf:top-1/2 esf:right-full esf:-translate-x-[10px] esf:-translate-y-1/2',
	default => 'esf:bottom-full esf:left-1/2 esf:-translate-x-1/2 esf:-translate-y-[10px]',
};

$tooltipClass = Helpers::clsx([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($tooltipPosition, $componentClass, '', $tooltipPosition),
	Helpers::selector($selectorClass, $selectorClass, $componentClass),
	Helpers::selector($additionalTooltipClass, $additionalTooltipClass),
	'esf:relative esf:cursor-pointer esf:inline-flex esf:group/tooltip',
	'esf:hover:text-accent-600',
]);

?>

<span class="<?php echo esc_attr($tooltipClass); ?>">
	<?php echo UtilsHelper::getUtilsIcons('tooltip'); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped
	?>
	<span class="<?php echo esc_attr("{$componentClass}__inner"); ?> <?php echo esc_attr($tooltipInnerPositionClass); ?> esf:bg-accent-600 esf:text-white esf:rounded esf:hidden esf:group-hover/tooltip:block esf:p-[5px_10px] esf:absolute esf:z-[999] esf:w-150 esf:text-[11px] esf:leading-snug">
		<?php echo esc_html($tooltipContent); ?>
	</span>
</span>
