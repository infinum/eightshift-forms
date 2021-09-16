<?php

/**
 * Template for the fieldset Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$fieldsetLegend = Components::checkAttr('fieldsetLegend', $attributes, $manifest);
$fieldsetId = Components::checkAttr('fieldsetId', $attributes, $manifest);
$fieldsetName = Components::checkAttr('fieldsetName', $attributes, $manifest);
$fieldsetContent = Components::checkAttr('fieldsetContent', $attributes, $manifest);

$fieldsetClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
]);

?>

<fieldset
	class="<?php echo esc_attr($fieldsetClass); ?>"
	name="<?php echo esc_attr($fieldsetName); ?>"
	id="<?php echo esc_attr($fieldsetId); ?>"
>
	<?php if ($fieldsetLegend) { ?>
		<legend class="<?php echo esc_attr("{$componentClass}__legend"); ?>">
			<?php echo esc_html($fieldsetLegend); ?>
		</legend>
	<?php } ?>
	<div class="<?php echo esc_attr("{$componentClass}__content"); ?>">
		<?php echo $fieldsetContent; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
</fieldset>
