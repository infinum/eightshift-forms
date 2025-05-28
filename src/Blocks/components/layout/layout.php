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
$layoutTag = Helpers::checkAttr('layoutTag', $attributes, $manifest);
$layoutType = Helpers::checkAttr('layoutType', $attributes, $manifest);

$layoutClass = Helpers::classnames([
	Helpers::selector($componentClass, $componentClass),
	Helpers::selector($blockClass, $blockClass, $selectorClass),
	Helpers::selector($additionalClass, $additionalClass),
]);

$additionalAttributes = $attributes['additionalAttributes'] ?? [];

?>

<div
	class="es:bg-white es:rounded-xl es:border es:border-secondary-300 es:overflow-clip es:max-w-lg es:shadow-xs"
	data-layout-type="<?php echo esc_attr($layoutType); ?>"
	<?php
	foreach ($additionalAttributes as $key => $value) {
		if (!empty($key) && !empty($value)) {
			echo wp_kses_post(" {$key}='" . $value . "'");
		}
	}
	?>>
	<div class="es:divide-y es:divide-secondary-200/75 es:py-4">
		<div class="es:space-y-2.5 es:not-last:pb-4 es:not-first:pt-4 es:px-4">
			<?php echo $layoutContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped 
			?>
		</div>
	</div>
</div>
