<?php

/**
 * Template for the Tooltip Component.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftFormsUtils\Helpers\UtilsHelper;
use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalTooltipClass = $attributes['additionalTooltipClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$tooltipContent = Helpers::checkAttr('tooltipContent', $attributes, $manifest);
$tooltipPosition = Helpers::checkAttr('tooltipPosition', $attributes, $manifest);

$tooltipClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($tooltipPosition, $componentClass, '', $tooltipPosition),
	Helpers::selector($selectorClass, $selectorClass, $componentClass),
	Helpers::selector($additionalTooltipClass, $additionalTooltipClass),
]);

?>

<span class="<?php echo esc_attr($tooltipClass); ?>">
	<?php echo UtilsHelper::getUtilsIcons('tooltip'); // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>
	<span class="<?php echo esc_attr("{$componentClass}__inner"); ?>">
		<?php echo esc_html($tooltipContent); ?>
	</span>
</span>
