<?php

/**
 * Layout component view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$additionalClass = $attributes['additionalClass'] ?? '';

$layoutContent = Helpers::checkAttr('layoutContent', $attributes, $manifest);
$layoutType = Helpers::checkAttr('layoutType', $attributes, $manifest);

$additionalAttributes = $attributes['additionalAttributes'] ?? [];

?>

<div
	class="<?php echo esc_attr(Helpers::clsx([
						'esf:flex esf:p-20 esf:flex-col esf:gap-15 esf:bg-white esf:border esf:border-border esf:rounded-md esf:shadow-xs',
						$additionalClass,
					])); ?>"
	data-layout-type="<?php echo esc_attr($layoutType); ?>"
	<?php
	echo Helpers::getAttrsOutput($additionalAttributes);
	?>>
	<?php echo $layoutContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
	?>
</div>
