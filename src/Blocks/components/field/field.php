<?php

/**
 * Template for the Field Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Enqueue\Blocks\EnqueueBlocks;
use EightshiftForms\Helpers\Components;

$globalManifest = Components::getManifest(dirname(__DIR__, 2));
$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalFieldClass = $attributes['additionalFieldClass'] ?? '';
$blockClass = $attributes['blockClass'] ?? '';
$selectorClass = $attributes['selectorClass'] ?? $componentClass;

// Update media breakpoints from the filter.
$customMediaBreakpoints = apply_filters(EnqueueBlocks::FILTER_MEDIA_BREAKPOINTS_NAME, []);
if (
	has_filter(EnqueueBlocks::FILTER_MEDIA_BREAKPOINTS_NAME) &&
	is_array($customMediaBreakpoints) &&
	isset($customMediaBreakpoints['mobile']) &&
	isset($customMediaBreakpoints['tablet']) &&
	isset($customMediaBreakpoints['desktop']) &&
	isset($customMediaBreakpoints['large'])
) {
	$globalManifest['globalVariables']['breakpoints'] = $customMediaBreakpoints;
}

$unique = Components::getUnique();

$fieldLabel = Components::checkAttr('fieldLabel', $attributes, $manifest);
$fieldId = Components::checkAttr('fieldId', $attributes, $manifest);
$fieldName = Components::checkAttr('fieldName', $attributes, $manifest);
$fieldContent = Components::checkAttr('fieldContent', $attributes, $manifest);
$fieldType = Components::checkAttr('fieldType', $attributes, $manifest);
$fieldUseError = Components::checkAttr('fieldUseError', $attributes, $manifest);
$fieldHelp = Components::checkAttr('fieldHelp', $attributes, $manifest);
$fieldDisabled = Components::checkAttr('fieldDisabled', $attributes, $manifest);

$fieldClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($blockClass, $blockClass, $selectorClass),
	Components::selector($additionalFieldClass, $additionalFieldClass),
	Components::selector($fieldDisabled, $componentClass, '', 'disabled'),
]);

$fieldTag = 'div';
$labelTag = 'label';

if ($fieldType === 'fieldset') {
	$fieldTag = 'fieldset';
	$labelTag = 'legend';
}

?>

<<?php echo esc_attr($fieldTag); ?> class="<?php echo esc_attr($fieldClass); ?>" data-id="<?php echo esc_attr($unique); ?>">

	<?php echo Components::outputCssVariables($attributes, $manifest, $unique, $globalManifest); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

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
	<?php if ($fieldHelp) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__help"); ?>">
			<?php echo $fieldHelp; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php } ?>
	<?php
	if ($fieldUseError) {
		echo Components::render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'error',
			Components::props('error', $attributes, [
				'errorId' => $fieldName,
				'blockClass' => $componentClass
			])
		);
	}
	?>
</<?php echo esc_attr($fieldTag); ?>>
