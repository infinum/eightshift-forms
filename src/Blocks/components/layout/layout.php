<?php

/**
 * Layout component view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$layoutUse = Helpers::checkAttr('layoutUse', $attributes, $manifest);
if (!$layoutUse) {
	return;
}

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$layoutContent = Helpers::checkAttr('layoutContent', $attributes, $manifest);
$layoutType = Helpers::checkAttr('layoutType', $attributes, $manifest);

$additionalAttributes = $attributes['additionalAttributes'] ?? [];

?>

<div
	class="esf:flex esf:flex-col esf:gap-20 esf:p-20 esf:rounded-md esf:bg-white esf:shadow-xs"
	data-layout-type="<?php echo esc_attr($layoutType); ?>"
	<?php
	echo Helpers::getAttrsOutput($additionalAttributes);
	?>>
	<?php echo $layoutContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
	?>
</div>
