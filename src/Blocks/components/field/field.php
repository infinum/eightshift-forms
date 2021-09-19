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
$fieldContent = Components::checkAttr('fieldContent', $attributes, $manifest);
$fieldType = Components::checkAttr('fieldType', $attributes, $manifest);

$fieldClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalClass, $additionalClass),
]);

$fieldTag = 'div';
$labelTag = 'label';

if ($fieldType === 'fieldset') {
	$fieldTag = 'fieldset';
	$labelTag = 'legend';
}

?>

<<?php echo esc_attr($fieldTag); ?> class="<?php echo esc_attr($fieldClass); ?>">
	<?php if ($fieldLabel) { ?>
		<<?php echo esc_attr($labelTag); ?>
			class="<?php echo esc_attr("{$componentClass}__label"); ?>"
			for="<?php echo esc_attr($fieldId); ?>"
		>
			<?php echo esc_html($fieldLabel); ?>
		</<?php echo esc_attr($labelTag); ?>>
	<?php } ?>
	<div class="<?php echo esc_attr("{$componentClass}__content"); ?>">
		<?php echo $fieldContent; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	<?php
	echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'error',
		Components::props('error', $attributes, [
			'errorId' => $fieldId
		])
	);
	?>
</<?php echo esc_attr($fieldTag); ?>>
