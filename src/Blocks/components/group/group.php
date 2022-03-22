<?php

/**
 * Template for the group Component.
 *
 * @package EightshiftForms
 */

use EightshiftForms\Helpers\Components;

$manifest = Components::getManifest(__DIR__);

$componentClass = $manifest['componentClass'] ?? '';
$additionalGroupClass = $attributes['additionalGroupClass'] ?? '';
$componentJsClass = $manifest['componentJsClass'] ?? '';
$componentJsClassInner = $manifest['componentJsClassInner'] ?? '';

$groupLabel = Components::checkAttr('groupLabel', $attributes, $manifest);
$groupContent = Components::checkAttr('groupContent', $attributes, $manifest);
$groupId = Components::checkAttr('groupId', $attributes, $manifest);
$groupSaveOneField = Components::checkAttr('groupSaveOneField', $attributes, $manifest);
$groupStyle = Components::checkAttr('groupStyle', $attributes, $manifest);
$groupBeforeContent = Components::checkAttr('groupBeforeContent', $attributes, $manifest);
$groupHelp = Components::checkAttr('groupHelp', $attributes, $manifest);

$groupClass = Components::classnames([
	Components::selector($componentClass, $componentClass),
	Components::selector($additionalGroupClass, $additionalGroupClass),
	Components::selector($groupStyle, $componentClass, '', $groupStyle),
	Components::selector(!$groupSaveOneField && $componentJsClass, $componentJsClass),
	Components::selector($groupSaveOneField && $componentJsClassInner, $componentJsClassInner),
]);

?>

<div
	class="<?php echo esc_attr($groupClass); ?>"
	data-field-id="<?php echo esc_attr($groupId); ?>"
>

	<?php if ($groupLabel) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__label"); ?>">
			<?php echo esc_html($groupLabel); ?>
		</div>
	<?php } ?>

	<?php if ($groupBeforeContent) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__before-content"); ?>">
			<?php echo $groupBeforeContent; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php } ?>

	<?php if ($groupContent) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__content"); ?>">
			<?php echo $groupContent; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php } ?>

	<?php if ($groupHelp) { ?>
		<div class="<?php echo esc_attr("{$componentClass}__help"); ?>">
			<?php echo $groupHelp; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php } ?>
</div>
