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
$layoutWithBg = Helpers::checkAttr('layoutWithBg', $attributes, $manifest);

$additionalAttributes = $attributes['additionalAttributes'] ?? [];

?>

<div
	class="<?php echo esc_attr(Helpers::clsx([
						'esf:grid esf:gap-15 esf:grid-cols-12',
						$layoutWithBg ? 'esf:p-20 esf:bg-white esf:border esf:border-border esf:rounded-md' : '',
						$layoutType === 'layout-grid-half' ? "esf:items-center esf:[&>*]:col-span-6" : 'esf:items-center esf:[&>*]:col-span-12',
						$additionalClass,
					])); ?>"
	<?php
	echo wp_kses_post(Helpers::getAttrsOutput($additionalAttributes));
	?>>
	<?php echo $layoutContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
	?>
</div>
