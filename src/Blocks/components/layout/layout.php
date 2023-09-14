<?php

/**
 * Layout component view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$layoutUse = Components::checkAttr('layoutUse', $attributes, $manifest);
if (!$layoutUse) {
	return;
}

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$layoutContent = Components::checkAttr('layoutContent', $attributes, $manifest);
$layoutTag = Components::checkAttr('layoutTag', $attributes, $manifest);
$layoutType = Components::checkAttr('layoutType', $attributes, $manifest);

$layoutClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
]);

$additionalAttributes = $attributes['additionalAttributes'] ?? [];

?>

<<?php echo esc_attr($layoutTag); ?>
	class="<?php echo esc_attr($layoutClass); ?>"
	data-layout-type="<?php echo esc_attr($layoutType); ?>"
	<?php
	foreach ($additionalAttributes as $key => $value) {
		if (!empty($key) && !empty($value)) {
			echo wp_kses_post("{$key}=" . $value . " ");
		}
	}
	?>
>
	<div class="<?php echo esc_attr("{$componentClass}__wrap"); ?>">
		<?php echo $layoutContent; // phpcs:ignore Eightshift.Security.ComponentsEscape.OutputNotEscaped ?>
	</div>
</<?php echo esc_attr($layoutTag); ?>>
