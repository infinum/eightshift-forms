<?php

/**
 * Template for the Field Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalClass = $attributes['additionalClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

$fieldLabel = Components::checkAttr('fieldLabel', $attributes, $manifest);
$fieldId = Components::checkAttr('fieldId', $attributes, $manifest);
$fieldName = Components::checkAttr('fieldName', $attributes, $manifest);
$fieldContent = Components::checkAttr('fieldContent', $attributes, $manifest);

$fieldClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
]);

?>

<div
	class="<?php echo esc_attr($fieldClass); ?>"
	name="<?php echo esc_attr($fieldName); ?>"
>
	<?php if ($fieldLabel) { ?>
		<label
			class="<?php echo esc_attr("{$componentClass}__label"); ?>"
			for="<?php echo esc_attr($fieldId); ?>"
		>
			<?php echo esc_html($fieldLabel); ?>
		</label>
	<?php } ?>
	<div class="<?php echo esc_attr("{$componentClass}__content"); ?>">
		<?php echo $fieldContent; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
</div>
