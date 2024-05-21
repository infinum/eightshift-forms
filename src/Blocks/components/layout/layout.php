<?php

/**
 * Layout component view.
 *
 * @package EightshiftForms
 */

use EightshiftFormsVendor\EightshiftLibs\Helpers\Helpers;

$manifest = Helpers::getManifestByDir(__DIR__);

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

<<?php echo esc_attr($layoutTag); ?>
	class="<?php echo esc_attr($layoutClass); ?>"
	data-layout-type="<?php echo esc_attr($layoutType); ?>"
	<?php
	foreach ($additionalAttributes as $key => $value) {
		if (!empty($key) && !empty($value)) {
			echo wp_kses_post(" {$key}='" . $value . "'");
		}
	}
	?>
>
	<div class="<?php echo esc_attr("{$componentClass}__wrap"); ?>">
		<?php echo $layoutContent; // phpcs:ignore Eightshift.Security.HelpersEscape.OutputNotEscaped ?>
	</div>
</<?php echo esc_attr($layoutTag); ?>>
